<?php

namespace App\Services\Instagram;

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstagramAccountDashboardMetrics
{
    /**
     * @var list<string>
     */
    public const PERIOD_KEYS = [
        'posts',
        'reactions',
        'comments',
        'views',
        'reach',
        'saved',
        'engagement',
        'engagement_rate',
    ];

    /**
     * @return array{
     *     posts: int,
     *     reactions: int,
     *     comments: int,
     *     views: int,
     *     reach: int,
     *     saved: int,
     *     engagement: int,
     *     engagement_rate: float|null
     * }
     */
    public function forPeriod(InstagramAccount $account, Carbon $start, Carbon $end): array
    {
        $media = InstagramMedia::query()
            ->where('instagram_account_id', $account->id)
            ->whereBetween('timestamp', [$start, $end])
            ->get(['like_count', 'comments_count', 'view_count', 'insights']);

        return $this->aggregateMediaCollection($media, (int) $account->followers_count);
    }

    /**
     * @return array<string, array{value: mixed, previous: mixed, change_percent: float|null}>
     */
    public function comparePeriods(
        InstagramAccount $account,
        Carbon $start,
        Carbon $end,
        Carbon $previousStart,
        Carbon $previousEnd,
    ): array {
        $current = $this->forPeriod($account, $start, $end);
        $previous = $this->forPeriod($account, $previousStart, $previousEnd);

        $result = [];

        foreach (self::PERIOD_KEYS as $key) {
            $result[$key] = [
                'value' => $current[$key],
                'previous' => $previous[$key],
                'change_percent' => $this->changePercent($current[$key], $previous[$key]),
            ];
        }

        return $result;
    }

    /**
     * @return array{
     *     followers_count: int,
     *     follows_count: int,
     *     media_count: int,
     *     impressions: int|null,
     *     reach: int|null,
     *     profile_views: int|null,
     *     website_clicks: int|null,
     *     accounts_engaged: int|null
     * }
     */
    public function accountSnapshot(InstagramAccount $account): array
    {
        $insights = $account->latest_account_insights ?? [];

        return [
            'followers_count' => (int) $account->followers_count,
            'follows_count' => (int) $account->follows_count,
            'media_count' => (int) $account->media_count,
            'impressions' => $this->insightValue($insights, 'impressions'),
            'reach' => $this->insightValue($insights, 'reach'),
            'profile_views' => $this->insightValue($insights, 'profile_views'),
            'website_clicks' => $this->insightValue($insights, 'website_clicks'),
            'accounts_engaged' => $this->insightValue($insights, 'accounts_engaged'),
        ];
    }

    /**
     * @param  Collection<int, InstagramMedia>  $media
     * @return array{
     *     posts: int,
     *     reactions: int,
     *     comments: int,
     *     views: int,
     *     reach: int,
     *     saved: int,
     *     engagement: int,
     *     engagement_rate: float|null
     * }
     */
    protected function aggregateMediaCollection(Collection $media, int $followers): array
    {
        $posts = $media->count();
        $reactions = (int) $media->sum('like_count');
        $comments = (int) $media->sum('comments_count');
        $views = (int) $media->sum('view_count');

        $reach = 0;
        $saved = 0;
        $engagement = 0;

        foreach ($media as $item) {
            $reach += (int) ($item->insight('reach') ?? 0);
            $saved += (int) ($item->insight('saved') ?? 0);
            $engagement += (int) ($item->insight('engagement') ?? 0);
        }

        $engagementRate = null;

        if ($followers > 0) {
            $engagementRate = round((($reactions + $comments) / $followers) * 100, 1);
        }

        return [
            'posts' => $posts,
            'reactions' => $reactions,
            'comments' => $comments,
            'views' => $views,
            'reach' => $reach,
            'saved' => $saved,
            'engagement' => $engagement,
            'engagement_rate' => $engagementRate,
        ];
    }

    protected function changePercent(mixed $value, mixed $previous): ?float
    {
        if ($value === null || $previous === null) {
            return null;
        }

        $previousNumber = (float) $previous;
        $valueNumber = (float) $value;

        if ($previousNumber == 0.0) {
            return $valueNumber == 0.0 ? 0.0 : null;
        }

        return round((($valueNumber - $previousNumber) / $previousNumber) * 100, 1);
    }

    /**
     * @param  array<string, mixed>  $insights
     */
    protected function insightValue(array $insights, string $key): ?int
    {
        if (! array_key_exists($key, $insights) || $insights[$key] === null) {
            return null;
        }

        return (int) $insights[$key];
    }
}
