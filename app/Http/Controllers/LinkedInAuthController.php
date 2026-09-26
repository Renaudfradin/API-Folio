<?php

namespace App\Http\Controllers;

use App\Filament\Pages\LinkedInDashboard;
use App\Models\User;
use App\Services\LinkedIn\LinkedInOAuthService;
use App\Services\LinkedIn\LinkedInSyncService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LinkedInAuthController extends Controller
{
    private function dashboardRedirect(): RedirectResponse
    {
        return redirect(LinkedInDashboard::getUrl());
    }

    public function redirect(LinkedInOAuthService $oauth): RedirectResponse
    {
        $configurationError = $oauth->oauthConfigurationError();

        if ($configurationError !== null) {
            return $this->dashboardRedirect()
                ->with('error', $configurationError);
        }

        $state = Crypt::encrypt([
            'nonce' => (string) Str::uuid(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->away($oauth->buildAuthorizationUrl($state));
    }

    public function callback(
        Request $request,
        LinkedInOAuthService $oauth,
        LinkedInSyncService $syncService,
    ): RedirectResponse {
        if ($request->filled('error')) {
            return $this->dashboardRedirect()
                ->with('error', $request->string('error_description')->toString() ?: 'Connexion LinkedIn annulée.');
        }

        abort_unless($request->has('code'), 400, 'Code OAuth manquant.');

        $user = $this->resolveOAuthUser($request->string('state')->toString());

        try {
            $tokenData = $syncService->exchangeCodeForToken($request->string('code')->toString());
            $profileData = $syncService->fetchUserInfo($tokenData['access_token']);
        } catch (RequestException $exception) {
            Log::warning('LinkedIn OAuth token exchange failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->dashboardRedirect()
                ->with('error', 'Impossible de finaliser la connexion LinkedIn. Vérifiez l’URI de redirection et les identifiants de l’application.');
        }

        $connection = $syncService->persistConnection(
            $user,
            $user->linkedinConnection,
            $tokenData,
            $profileData
        );

        try {
            $syncService->syncConnection($connection);
        } catch (Throwable $throwable) {
            Log::warning('LinkedIn sync failed after OAuth connection.', [
                'linkedin_connection_id' => $connection->id,
                'message' => $throwable->getMessage(),
            ]);
        }

        return $this->dashboardRedirect()
            ->with('success', 'Compte LinkedIn connecté.');
    }

    private function resolveOAuthUser(string $state): User
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

        $user = User::query()->find((int) $payload['user_id']);

        abort_unless($user instanceof User, 403, 'Utilisateur OAuth invalide.');

        return $user;
    }
}
