<?php

namespace App\Services\Instagram;

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\InstagramSyncRun;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Throwable;

class InstagramSyncService
{
    public function __construct(
        protected InstagramGraphService $graph,
    ) {
    }

    /**
     * @return array<int, InstagramAccount>
     */
    public function syncAllActive(): array
    {
        return InstagramAccount::query()
            ->where('is_active', true)
            ->get()
            ->map(function (InstagramAccount $account): ?InstagramAccount {
                try {
                    return $this->syncAccount($account);
                } catch (Throwable) {
                    return null;
                }
            })
            ->filter()
            ->all();
    }

    public function syncAccount(InstagramAccount $account, bool $refreshProfile = true): InstagramAccount
    {
        $run = $account->syncRuns()->create([
            'status' => 'running',
            'started_at' => now(),
            'payload' => [
                'refresh_profile' => $refreshProfile,
            ],
        ]);

        try {
            $profile = $refreshProfile
                ? $this->graph->getInstagramAccount($account->business_account_id, $account->access_token)
                : [];

            $accountInsights = [];
            try {
                $accountInsights = $this->graph->getAccountInsights($account->business_account_id, $account->access_token);
            } catch (Throwable) {
                $accountInsights = [];
            }

            $mediaItems = $this->graph->getMedia($account->business_account_id, $account->access_token);

            $profileInsights = $this->normalizeInsights($accountInsights);
            $syncedCount = 0;

            foreach ($mediaItems as $mediaItem) {
                $mediaInsightsPayload = [];

                try {
                    $mediaInsightsPayload = $this->graph->getMediaInsights($mediaItem['id'], $account->access_token);
                } catch (Throwable) {
                    $mediaInsightsPayload = [];
                }

                $mediaInsights = $this->normalizeInsights($mediaInsightsPayload);

                InstagramMedia::query()->updateOrCreate(
                    ['media_id' => $mediaItem['id']],
                    [
                        'instagram_account_id' => $account->id,
                        'caption' => $mediaItem['caption'] ?? null,
                        'permalink' => $mediaItem['permalink'] ?? null,
                        'media_type' => $mediaItem['media_type'] ?? 'UNKNOWN',
                        'media_product_type' => $mediaItem['media_product_type'] ?? null,
                        'media_url' => $mediaItem['media_url'] ?? null,
                        'thumbnail_url' => $mediaItem['thumbnail_url'] ?? null,
                        'like_count' => (int) ($mediaItem['like_count'] ?? 0),
                        'comments_count' => (int) ($mediaItem['comments_count'] ?? 0),
                        'view_count' => (int) Arr::get($mediaInsights, 'views', Arr::get($mediaInsights, 'impressions', 0)),
                        'timestamp' => isset($mediaItem['timestamp']) ? Carbon::parse($mediaItem['timestamp']) : null,
                        'insights' => $mediaInsights,
                        'raw_data' => $mediaItem,
                        'synced_at' => now(),
                    ]
                );

                $syncedCount++;
            }

            $account->forceFill([
                'page_id' => $account->page_id,
                'page_name' => $account->page_name,
                'business_account_id' => $profile['id'] ?? $account->business_account_id,
                'username' => $profile['username'] ?? $account->username,
                'name' => $profile['name'] ?? $account->name,
                'biography' => $profile['biography'] ?? $account->biography,
                'website' => $profile['website'] ?? $account->website,
                'profile_picture_url' => $profile['profile_picture_url'] ?? $account->profile_picture_url,
                'followers_count' => (int) ($profile['followers_count'] ?? $account->followers_count),
                'follows_count' => (int) ($profile['follows_count'] ?? $account->follows_count),
                'media_count' => (int) ($profile['media_count'] ?? $account->media_count),
                'latest_account_insights' => $profileInsights,
                'last_synced_at' => now(),
                'last_synced_status' => 'success',
                'last_synced_error' => null,
            ])->save();

            $run->forceFill([
                'status' => 'success',
                'finished_at' => now(),
                'records_synced' => $syncedCount,
                'payload' => [
                    'refresh_profile' => $refreshProfile,
                    'account_insights' => $profileInsights,
                    'media_count' => count($mediaItems),
                ],
            ])->save();
        } catch (Throwable $throwable) {
            $account->forceFill([
                'last_synced_at' => now(),
                'last_synced_status' => 'failed',
                'last_synced_error' => $throwable->getMessage(),
            ])->save();

            $run->forceFill([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $throwable->getMessage(),
            ])->save();

            throw $throwable;
        }

        return $account->refresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalizeInsights(array $payload): array
    {
        return collect($payload['data'] ?? [])
            ->mapWithKeys(function (array $item): array {
                $value = $item['values'][0]['value'] ?? null;

                return [$item['name'] => $value];
            })
            ->all();
    }
}
