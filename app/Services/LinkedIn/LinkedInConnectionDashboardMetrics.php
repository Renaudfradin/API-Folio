<?php

namespace App\Services\LinkedIn;

use App\Models\LinkedinConnection;
use App\Models\LinkedinPost;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LinkedInConnectionDashboardMetrics
{
    /**
     * @var list<string>
     */
    public const PERIOD_KEYS = [
        'posts',
        'reactions',
        'comments',
        'shares',
        'impressions',
        'reach',
        'video_views',
        'engagement_rate',
    ];

    /**
     * @return array{
     *     followers_count: int,
     *     posts_count: int,
     *     reactions: int,
     *     comments: int,
     *     shares: int,
     *     impressions: int,
     *     reach: int,
     *     video_views: int,
     *     engagement_rate: float|null
     * }
     */
    public function accountSnapshot(LinkedinConnection $connection): array
    {
        $posts = LinkedinPost::query()
            ->where('linkedin_connection_id', $connection->id)
            ->get([
                'reactions',
                'comments',
                'shares',
                'impressions',
                'reach',
                'video_views',
                'engagement_rate',
            ]);

        $aggregated = $this->aggregatePostsCollection($posts, (int) $connection->followers_count);

        return [
            'followers_count' => (int) $connection->followers_count,
            'posts_count' => $posts->count(),
            'reactions' => $aggregated['reactions'],
            'comments' => $aggregated['comments'],
            'shares' => $aggregated['shares'],
            'impressions' => $aggregated['impressions'],
            'reach' => $aggregated['reach'],
            'video_views' => $aggregated['video_views'],
            'engagement_rate' => $aggregated['engagement_rate'],
        ];
    }

    /**
     * @return array<string, array{value: mixed, previous: mixed, change_percent: float|null}>
     */
    public function comparePeriods(
        LinkedinConnection $connection,
        Carbon $start,
        Carbon $end,
        Carbon $previousStart,
        Carbon $previousEnd,
    ): array {
        $current = $this->forPeriod($connection, $start, $end);
        $previous = $this->forPeriod($connection, $previousStart, $previousEnd);

        $result = [];

        foreach (self::PERIOD_KEYS as $key) {
            $result[$key] = [
                'value' => $current[$key],
                'previous' => $previous[$key],
                'change_percent' => $this->changePercent($current[$key], $previous[$key]),
            ];
        }

        $result['followers'] = [
            'value' => (int) $connection->followers_count,
            'previous' => (int) $connection->followers_count,
            'change_percent' => 0.0,
        ];

        return $result;
    }

    /**
     * @return array{
     *     posts: int,
     *     reactions: int,
     *     comments: int,
     *     shares: int,
     *     impressions: int,
     *     reach: int,
     *     video_views: int,
     *     engagement_rate: float|null
     * }
     */
    public function forPeriod(LinkedinConnection $connection, Carbon $start, Carbon $end): array
    {
        $posts = LinkedinPost::query()
            ->where('linkedin_connection_id', $connection->id)
            ->whereBetween('published_at', [$start, $end])
            ->get([
                'reactions',
                'comments',
                'shares',
                'impressions',
                'reach',
                'video_views',
                'engagement_rate',
            ]);

        return $this->aggregatePostsCollection($posts, (int) $connection->followers_count);
    }

    /**
     * @param  Collection<int, LinkedinPost>  $posts
     * @return array{
     *     posts: int,
     *     reactions: int,
     *     comments: int,
     *     shares: int,
     *     impressions: int,
     *     reach: int,
     *     video_views: int,
     *     engagement_rate: float|null
     * }
     */
    protected function aggregatePostsCollection(Collection $posts, int $followers): array
    {
        $postCount = $posts->count();
        $reactions = (int) $posts->sum('reactions');
        $comments = (int) $posts->sum('comments');
        $shares = (int) $posts->sum('shares');
        $impressions = (int) $posts->sum('impressions');
        $reach = (int) $posts->sum('reach');
        $videoViews = (int) $posts->sum('video_views');

        $engagementRate = null;

        if ($impressions > 0) {
            $engagementRate = round((($reactions + $comments + $shares) / $impressions) * 100, 1);
        } elseif ($followers > 0 && $postCount > 0) {
            $engagementRate = round((($reactions + $comments + $shares) / $followers) * 100, 1);
        }

        return [
            'posts' => $postCount,
            'reactions' => $reactions,
            'comments' => $comments,
            'shares' => $shares,
            'impressions' => $impressions,
            'reach' => $reach,
            'video_views' => $videoViews,
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
}
