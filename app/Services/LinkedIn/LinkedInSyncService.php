<?php

namespace App\Services\LinkedIn;

use App\Models\LinkedinConnection;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LinkedInSyncService
{
    private const TOKEN_ENDPOINT = 'https://www.linkedin.com/oauth/v2/accessToken';
    private const USERINFO_ENDPOINT = 'https://api.linkedin.com/v2/userinfo';

    public function __construct(
        private readonly LinkedInOAuthService $oauth,
        private readonly LinkedInApiClient $apiClient,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()
            ->post(self::TOKEN_ENDPOINT, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
                'client_id' => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret'),
            ]);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshAccessToken(LinkedinConnection $connection): array
    {
        if (! $connection->refresh_token) {
            throw new RuntimeException('LinkedIn refresh token is missing.');
        }

        $response = Http::asForm()
            ->post(self::TOKEN_ENDPOINT, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
                'redirect_uri' => $this->redirectUri(),
                'client_id' => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret'),
            ]);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get(self::USERINFO_ENDPOINT);

        $response->throw();

        return $response->json();
    }

    public function syncCurrentUser(User $user): LinkedinConnection
    {
        $connection = $user->linkedinConnection;

        if (! $connection instanceof LinkedinConnection) {
            throw new RuntimeException('Aucune connexion LinkedIn n’est configurée.');
        }

        return $this->syncConnection($connection);
    }

    public function syncConnection(LinkedinConnection $connection): LinkedinConnection
    {
        $user = $connection->user;

        if (! $user instanceof User) {
            throw new RuntimeException('Utilisateur LinkedIn introuvable.');
        }

        $connection = $this->ensureValidToken($connection, $user);

        $errors = [];
        $profileSynced = false;

        try {
            $profileData = $this->fetchUserInfo($connection->access_token);
            $connection = $this->persistConnection($user, $connection, [], $profileData);
            $profileSynced = true;
        } catch (Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        }

        try {
            $followers = $this->apiClient->fetchMemberFollowersCount($connection);

            if ($followers !== null) {
                $connection->forceFill(['followers_count' => $followers])->save();
            }
        } catch (Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        }

        try {
            $this->apiClient->syncMemberPosts($connection);
        } catch (Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        }

        $status = $profileSynced ? 'ok' : 'failed';
        $errorMessage = $errors === [] ? null : Str::limit(implode(' · ', array_unique($errors)), 500);

        $connection->forceFill([
            'last_synced_at' => now(),
            'last_synced_status' => $status,
            'last_synced_error' => $errorMessage,
        ])->save();

        $user->forceFill(['linkedin_synced_at' => now()])->save();

        return $connection->refresh();
    }

    public function syncAllActive(): void
    {
        LinkedinConnection::query()
            ->whereNotNull('access_token')
            ->each(fn (LinkedinConnection $connection) => $this->syncConnection($connection));
    }

    protected function ensureValidToken(LinkedinConnection $connection, User $user): LinkedinConnection
    {
        if ($connection->expires_at && $connection->expires_at->isPast()) {
            $tokenData = $this->refreshAccessToken($connection);

            return $this->persistConnection($user, $connection, $tokenData, $connection->raw_profile ?? []);
        }

        if (! $connection->access_token) {
            throw new RuntimeException('Le token LinkedIn est manquant.');
        }

        return $connection;
    }

    public function disconnect(User $user): void
    {
        $user->linkedinConnection?->delete();

        $user->forceFill([
            'linkedin_profile_id' => null,
            'linkedin_url' => null,
            'linkedin_headline' => null,
            'linkedin_synced_at' => null,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $tokenData
     * @param  array<string, mixed>  $profileData
     */
    public function persistConnection(
        User $user,
        ?LinkedinConnection $connection,
        array $tokenData,
        array $profileData
    ): LinkedinConnection {
        $tokenData = $tokenData ?: [];

        $payload = array_filter([
            'provider_user_id' => data_get($profileData, 'sub') ?? data_get($connection, 'provider_user_id'),
            'access_token' => Arr::get($tokenData, 'access_token', $connection?->access_token),
            'refresh_token' => Arr::get($tokenData, 'refresh_token', $connection?->refresh_token),
            'expires_at' => array_key_exists('expires_in', $tokenData)
                ? now()->addSeconds((int) $tokenData['expires_in'])
                : $connection?->expires_at,
            'scopes' => $this->scopesFromTokenData($tokenData, $connection),
            'profile_url' => data_get($profileData, 'profile') ?? data_get($profileData, 'publicProfileUrl') ?? $connection?->profile_url,
            'profile_name' => data_get($profileData, 'name') ?? data_get($profileData, 'localizedName') ?? $connection?->profile_name,
            'profile_picture_url' => data_get($profileData, 'picture') ?? data_get($profileData, 'profilePicture') ?? $connection?->profile_picture_url,
            'last_synced_at' => now(),
            'raw_profile' => $profileData ?: $connection?->raw_profile,
        ], fn ($value) => ! is_null($value));

        if ($connection) {
            $connection->forceFill($payload)->save();
        } else {
            $connection = $user->linkedinConnection()->create($payload);
        }

        $user->forceFill([
            'name' => data_get($profileData, 'name', $user->name),
            'linkedin_profile_id' => data_get($profileData, 'sub', $user->linkedin_profile_id),
            'linkedin_url' => $payload['profile_url'] ?? $user->linkedin_url,
            'linkedin_headline' => data_get($profileData, 'headline', $user->linkedin_headline),
            'linkedin_synced_at' => now(),
        ])->save();

        return $connection->refresh();
    }

    /**
     * @param  array<string, mixed>  $tokenData
     */
    private function scopesFromTokenData(array $tokenData, ?LinkedinConnection $connection): array
    {
        if (filled($tokenData['scope'] ?? null)) {
            return preg_split('/\s+/', trim((string) $tokenData['scope'])) ?: [];
        }

        return $connection?->scopes ?? $this->oauth->configuredScopes();
    }

    private function redirectUri(): string
    {
        return $this->oauth->redirectUri();
    }
}
