<?php

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Services\Instagram\InstagramAccountDashboardMetrics;
use App\Services\Instagram\InstagramDashboardDateRange;

it('aggregates loaded media metrics for a collection', function (): void {
    $service = new InstagramAccountDashboardMetrics;

    $media = collect([
        new InstagramMedia([
            'like_count' => 100,
            'comments_count' => 20,
            'view_count' => 300,
            'insights' => ['reach' => 40, 'saved' => 5, 'engagement' => 120],
        ]),
        new InstagramMedia([
            'like_count' => 50,
            'comments_count' => 10,
            'view_count' => 150,
            'insights' => ['reach' => 30, 'saved' => 2, 'engagement' => 60],
        ]),
    ]);

    $method = new ReflectionMethod($service, 'aggregateMediaCollection');

    $metrics = $method->invoke($service, $media, 1000);

    expect($metrics['posts'])->toBe(2)
        ->and($metrics['reactions'])->toBe(150)
        ->and($metrics['comments'])->toBe(30)
        ->and($metrics['views'])->toBe(450)
        ->and($metrics['reach'])->toBe(70)
        ->and($metrics['saved'])->toBe(7)
        ->and($metrics['engagement'])->toBe(180)
        ->and($metrics['engagement_rate'])->toBe(18.0);
});

it('computes change percent with zero-safe rules', function (): void {
    $service = new InstagramAccountDashboardMetrics;
    $method = new ReflectionMethod($service, 'changePercent');

    expect($method->invoke($service, 20, 10))->toBe(100.0)
        ->and($method->invoke($service, 0, 0))->toBe(0.0)
        ->and($method->invoke($service, 5, 0))->toBeNull()
        ->and($method->invoke($service, null, 10))->toBeNull();
});

it('returns null engagement rate without followers', function (): void {
    $service = new InstagramAccountDashboardMetrics;
    $method = new ReflectionMethod($service, 'aggregateMediaCollection');

    $metrics = $method->invoke($service, collect([
        new InstagramMedia([
            'like_count' => 5,
            'comments_count' => 1,
        ]),
    ]), 0);

    expect($metrics['engagement_rate'])->toBeNull();
});

it('builds account snapshot from profile and insights json', function (): void {
    $account = new InstagramAccount([
        'followers_count' => 91,
        'follows_count' => 87,
        'media_count' => 8,
        'latest_account_insights' => [
            'impressions' => 1200,
            'reach' => 900,
            'profile_views' => 45,
            'website_clicks' => 3,
            'accounts_engaged' => 12,
        ],
    ]);

    $snapshot = (new InstagramAccountDashboardMetrics)->accountSnapshot($account);

    expect($snapshot['followers_count'])->toBe(91)
        ->and($snapshot['follows_count'])->toBe(87)
        ->and($snapshot['media_count'])->toBe(8)
        ->and($snapshot['impressions'])->toBe(1200)
        ->and($snapshot['accounts_engaged'])->toBe(12);
});

it('builds dashboard date ranges for supported periods', function (): void {
    $now = now()->startOfDay()->addHours(12);

    $thirtyDays = InstagramDashboardDateRange::forPeriod('30d', $now);
    $sevenDays = InstagramDashboardDateRange::forPeriod('7d', $now);

    expect((int) ($thirtyDays['start']->startOfDay()->diffInDays($thirtyDays['end']->startOfDay()) + 1))->toBe(30)
        ->and((int) ($sevenDays['start']->startOfDay()->diffInDays($sevenDays['end']->startOfDay()) + 1))->toBe(7)
        ->and($thirtyDays['previousEnd']->lt($thirtyDays['start']))->toBeTrue();
});
