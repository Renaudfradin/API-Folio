<?php

namespace Tests\Unit\LinkedIn;

use App\Models\LinkedinConnection;
use App\Models\LinkedinPost;
use App\Models\User;
use App\Services\LinkedIn\LinkedInConnectionDashboardMetrics;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkedInConnectionDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_posts_for_a_period(): void
    {
        $user = User::factory()->create();
        $connection = LinkedinConnection::query()->create([
            'user_id' => $user->id,
            'provider_user_id' => 'member-1',
            'followers_count' => 1000,
        ]);

        LinkedinPost::query()->create([
            'linkedin_connection_id' => $connection->id,
            'post_urn' => 'urn:li:share:1',
            'published_at' => Carbon::parse('2026-09-10 12:00:00'),
            'reactions' => 10,
            'comments' => 2,
            'shares' => 1,
            'impressions' => 100,
            'reach' => 80,
            'video_views' => 5,
        ]);

        LinkedinPost::query()->create([
            'linkedin_connection_id' => $connection->id,
            'post_urn' => 'urn:li:share:2',
            'published_at' => Carbon::parse('2026-08-01 12:00:00'),
            'reactions' => 5,
            'comments' => 1,
            'shares' => 0,
            'impressions' => 50,
            'reach' => 40,
            'video_views' => 0,
        ]);

        $metrics = app(LinkedInConnectionDashboardMetrics::class)->forPeriod(
            $connection,
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-30'),
        );

        $this->assertSame(1, $metrics['posts']);
        $this->assertSame(10, $metrics['reactions']);
        $this->assertSame(2, $metrics['comments']);
        $this->assertSame(1, $metrics['shares']);
        $this->assertSame(100, $metrics['impressions']);
        $this->assertSame(13.0, $metrics['engagement_rate']);
    }

    public function test_it_builds_account_snapshot_from_all_posts(): void
    {
        $user = User::factory()->create();
        $connection = LinkedinConnection::query()->create([
            'user_id' => $user->id,
            'provider_user_id' => 'member-2',
            'followers_count' => 2600,
        ]);

        LinkedinPost::query()->create([
            'linkedin_connection_id' => $connection->id,
            'post_urn' => 'urn:li:share:3',
            'published_at' => now(),
            'reactions' => 3,
            'comments' => 1,
            'shares' => 0,
            'impressions' => 20,
        ]);

        $snapshot = app(LinkedInConnectionDashboardMetrics::class)->accountSnapshot($connection);

        $this->assertSame(2600, $snapshot['followers_count']);
        $this->assertSame(1, $snapshot['posts_count']);
        $this->assertSame(3, $snapshot['reactions']);
        $this->assertSame(20.0, $snapshot['engagement_rate']);
    }
}
