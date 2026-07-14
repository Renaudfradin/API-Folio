<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramGraphService;
use App\Services\Instagram\InstagramSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class InstagramOAuthController extends Controller
{
    public function redirect(InstagramGraphService $graph): RedirectResponse
    {
        $state = (string) Str::uuid();

        session(['instagram_oauth_state' => $state]);

        return redirect()->away($graph->buildOAuthUrl($state));
    }

    public function callback(
        Request $request,
        InstagramGraphService $graph,
        InstagramSyncService $syncService,
    ): RedirectResponse {
        if ($request->filled('error')) {
            return redirect()
                ->route('filament.admin.resources.instagram-accounts.index')
                ->with('error', $request->string('error_description')->toString() ?: 'Connexion Instagram annulée.');
        }

        abort_unless($request->has('code'), 400, 'Code OAuth manquant.');
        abort_unless($request->string('state')->toString() === session('instagram_oauth_state'), 403, 'État OAuth invalide.');

        $shortLivedTokenResponse = $graph->exchangeCodeForAccessToken($request->string('code')->toString());
        $tokenResponse = $shortLivedTokenResponse;

        try {
            $tokenResponse = $graph->exchangeForLongLivedToken($shortLivedTokenResponse['access_token']);
        } catch (Throwable) {
            // On conserve le token court si l'échange long terme échoue.
        }

        $accessToken = $tokenResponse['access_token'] ?? $shortLivedTokenResponse['access_token'] ?? null;

        abort_unless($accessToken, 422, 'Impossible de récupérer un access token Instagram.');

        $pages = $graph->getPages($accessToken);
        $page = collect($pages)->first(fn (array $page): bool => filled(data_get($page, 'instagram_business_account.id')));

        abort_unless($page, 422, 'Aucune Page Facebook liée à un compte Instagram professionnel n’a été trouvée.');

        $pageAccessToken = $page['access_token'] ?? $accessToken;
        $instagramBusinessAccountId = data_get($page, 'instagram_business_account.id');

        abort_unless($instagramBusinessAccountId, 422, 'Le compte Instagram professionnel lié est introuvable.');

        $profile = $graph->getInstagramAccount($instagramBusinessAccountId, $pageAccessToken);

        $account = InstagramAccount::query()->updateOrCreate(
            ['business_account_id' => $instagramBusinessAccountId],
            [
                'user_id' => Auth::id(),
                'page_id' => $page['id'] ?? null,
                'page_name' => $page['name'] ?? null,
                'username' => $profile['username'] ?? data_get($page, 'instagram_business_account.username'),
                'name' => $profile['name'] ?? null,
                'biography' => $profile['biography'] ?? null,
                'website' => $profile['website'] ?? null,
                'profile_picture_url' => $profile['profile_picture_url'] ?? null,
                'access_token' => $pageAccessToken,
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

        session()->forget('instagram_oauth_state');

        return redirect()
            ->route('filament.admin.resources.instagram-accounts.index')
            ->with('success', 'Compte Instagram connecté et synchronisé.');
    }
}
