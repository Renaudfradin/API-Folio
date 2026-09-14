<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramGraphService;
use App\Services\Instagram\InstagramSyncService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class InstagramOAuthController extends Controller
{
    public function redirect(InstagramGraphService $graph): RedirectResponse
    {
        $configurationError = $graph->oauthConfigurationError();

        if ($configurationError !== null) {
            return redirect()
                ->route('filament.admin.resources.instagram-accounts.index')
                ->with('error', $configurationError);
        }

        $state = Crypt::encrypt([
            'nonce' => (string) Str::uuid(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->away($graph->buildOAuthUrl($state));
    }

    public function callback(
        Request $request,
        InstagramGraphService $graph,
        InstagramSyncService $syncService,
    ): RedirectResponse {
        $indexRoute = 'filament.admin.resources.instagram-accounts.index';

        if ($request->filled('error')) {
            return redirect()
                ->route($indexRoute)
                ->with('error', $request->string('error_description')->toString() ?: 'Connexion Instagram annulée.');
        }

        abort_unless($request->has('code'), 400, 'Code OAuth manquant.');

        $userId = $this->resolveOAuthUserId($request->string('state')->toString());

        try {
            $shortLivedTokenResponse = $graph->exchangeCodeForAccessToken($request->string('code')->toString());
        } catch (RequestException $exception) {
            Log::warning('Instagram OAuth token exchange failed.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route($indexRoute)
                ->with('error', 'Impossible d’échanger le code Instagram. Vérifiez l’URI de redirection et les identifiants Meta.');
        }

        $tokenResponse = $shortLivedTokenResponse;
        $expiresIn = null;

        try {
            $tokenResponse = $graph->exchangeForLongLivedToken($shortLivedTokenResponse['access_token']);
            $expiresIn = $tokenResponse['expires_in'] ?? null;
        } catch (Throwable) {
            // On conserve le token court si l'échange long terme échoue.
        }

        $accessToken = $tokenResponse['access_token'] ?? $shortLivedTokenResponse['access_token'] ?? null;

        abort_unless($accessToken, 422, 'Impossible de récupérer un access token Instagram.');

        try {
            $profile = $graph->getAuthenticatedUser($accessToken);
        } catch (RequestException $exception) {
            Log::warning('Instagram profile fetch failed after OAuth.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route($indexRoute)
                ->with('error', 'Connexion refusée ou compte non professionnel. Convertissez votre compte en Business ou Creator sur Instagram, puis réessayez.');
        }

        $instagramUserId = (string) ($profile['user_id'] ?? $profile['id'] ?? $shortLivedTokenResponse['user_id'] ?? '');

        abort_unless($instagramUserId !== '', 422, 'Identifiant du compte Instagram introuvable.');

        $account = InstagramAccount::query()->updateOrCreate(
            ['business_account_id' => $instagramUserId],
            [
                'user_id' => $userId,
                'page_id' => null,
                'page_name' => null,
                'username' => $profile['username'] ?? null,
                'name' => $profile['name'] ?? null,
                'biography' => $profile['biography'] ?? null,
                'website' => $profile['website'] ?? null,
                'profile_picture_url' => $profile['profile_picture_url'] ?? null,
                'access_token' => $accessToken,
                'token_expires_at' => filled($expiresIn) ? now()->addSeconds((int) $expiresIn) : null,
                'followers_count' => (int) ($profile['followers_count'] ?? 0),
                'follows_count' => (int) ($profile['follows_count'] ?? 0),
                'media_count' => (int) ($profile['media_count'] ?? 0),
                'last_synced_status' => 'connected',
                'is_active' => true,
            ]
        );

        try {
            $syncService->syncAccount($account);
        } catch (Throwable $throwable) {
            Log::warning('Instagram sync failed after OAuth connection.', [
                'instagram_account_id' => $account->id,
                'message' => $throwable->getMessage(),
            ]);
        }

        return redirect()
            ->route($indexRoute)
            ->with('success', 'Compte Instagram connecté et synchronisé.');
    }

    private function resolveOAuthUserId(string $state): int
    {
        try {
            $payload = Crypt::decrypt($state);
        } catch (DecryptException) {
            abort(403, 'État OAuth invalide.');
        }

        abort_unless(
            is_array($payload)
            && filled($payload['nonce'] ?? null)
            && filled($payload['user_id'] ?? null),
            403,
            'État OAuth invalide.',
        );

        return (int) $payload['user_id'];
    }
}
