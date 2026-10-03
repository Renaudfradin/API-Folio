<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class InstagramWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $verifyToken = (string) config('services.instagram.webhook_verify_token', '');

        if (
            $request->string('hub_mode')->toString() === 'subscribe'
            && $verifyToken !== ''
            && $request->string('hub_verify_token')->toString() === $verifyToken
        ) {
            return response($request->string('hub_challenge')->toString(), 200);
        }

        abort(403);
    }

    public function handle(Request $request, InstagramSyncService $syncService): Response
    {
        $payload = $request->all();

        if (($payload['object'] ?? null) !== 'instagram') {
            Log::info('Instagram webhook ignored: unexpected object.', [
                'object' => $payload['object'] ?? null,
            ]);

            return response('OK', 200);
        }

        Log::info('Instagram webhook received.', [
            'entries' => count($payload['entry'] ?? []),
        ]);

        foreach ($payload['entry'] ?? [] as $entry) {
            $accountId = (string) ($entry['id'] ?? '');
            $account = InstagramAccount::query()
                ->where('business_account_id', $accountId)
                ->where('is_active', true)
                ->first();

            if ($account === null) {
                Log::warning('Instagram webhook: no matching account.', [
                    'entry_id' => $accountId,
                ]);

                continue;
            }

            foreach ($entry['changes'] ?? [] as $change) {
                $field = $change['field'] ?? null;
                $value = $change['value'] ?? [];

                if (! is_array($value)) {
                    continue;
                }

                if ($field === 'comments') {
                    Log::info('Instagram webhook comment event.', [
                        'instagram_account_id' => $account->id,
                        'comment_id' => Arr::get($value, 'id'),
                        'media_id' => Arr::get($value, 'media.id') ?? Arr::get($value, 'media_id'),
                    ]);

                    try {
                        $syncService->upsertWebhookComment($account, $value);
                    } catch (\Throwable $throwable) {
                        Log::warning('Instagram webhook comment upsert failed.', [
                            'instagram_account_id' => $account->id,
                            'message' => $throwable->getMessage(),
                            'comment_id' => Arr::get($value, 'id'),
                        ]);
                    }

                    continue;
                }

                if ($field === 'story_insights') {
                    try {
                        $syncService->upsertWebhookStoryInsights($account, $value);
                    } catch (\Throwable $throwable) {
                        Log::warning('Instagram webhook story insights upsert failed.', [
                            'instagram_account_id' => $account->id,
                            'message' => $throwable->getMessage(),
                            'media_id' => Arr::get($value, 'media_id') ?? Arr::get($value, 'id'),
                        ]);
                    }
                }
            }
        }

        return response('OK', 200);
    }
}
