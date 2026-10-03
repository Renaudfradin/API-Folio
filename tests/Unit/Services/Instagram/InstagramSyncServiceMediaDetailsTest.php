<?php

use App\Services\Instagram\InstagramSyncService;

it('normalizes story insights with total value and reactions alias', function (): void {
    $service = app(InstagramSyncService::class);

    $normalized = $service->normalizeStoryInsights([
        'data' => [
            ['name' => 'views', 'values' => [['value' => 99]]],
            ['name' => 'reach', 'values' => [['value' => 55]]],
            ['name' => 'likes', 'values' => [['value' => 6]]],
            ['name' => 'navigation', 'total_value' => ['value' => 12]],
        ],
    ]);

    expect($normalized['views'])->toBe(99)
        ->and($normalized['reach'])->toBe(55)
        ->and($normalized['reactions'])->toBe(6)
        ->and($normalized['navigation'])->toBe(12);
});

it('normalizes media children payload', function (): void {
    $service = app(InstagramSyncService::class);

    $normalized = $service->normalizeMediaChildren([
        ['id' => '1', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/a.jpg', 'thumbnail_url' => null],
        ['id' => '2', 'media_type' => 'IMAGE', 'media_url' => null, 'thumbnail_url' => null],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['media_url'])->toBe('https://example.com/a.jpg');
});

it('normalizes media comments payload', function (): void {
    $service = app(InstagramSyncService::class);

    $normalized = $service->normalizeMediaComments([
        [
            'id' => '99',
            'username' => 'fan',
            'text' => 'Super photo',
            'like_count' => 2,
            'timestamp' => '2026-01-15T10:00:00+0000',
        ],
        [
            'id' => '100',
            'username' => 'bot',
            'text' => '',
            'like_count' => 0,
            'timestamp' => null,
        ],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['username'])->toBe('fan')
        ->and($normalized[0]['text'])->toBe('Super photo');
});

it('normalizes media comments payload with from.username', function (): void {
    $service = app(InstagramSyncService::class);

    $normalized = $service->normalizeMediaComments([
        [
            'id' => '101',
            'from' => ['id' => '1', 'username' => 'fan_from'],
            'text' => 'Hello',
            'like_count' => 0,
            'timestamp' => '2026-01-15T10:00:00+0000',
        ],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['username'])->toBe('fan_from');
});

it('normalizes media comments with nested replies', function (): void {
    $service = app(InstagramSyncService::class);

    $normalized = $service->normalizeMediaComments([
        [
            'id' => '1',
            'username' => 'parent',
            'text' => 'Top level',
            'like_count' => 1,
            'timestamp' => '2026-01-15T10:00:00+0000',
            'replies' => [
                'data' => [
                    [
                        'id' => '2',
                        'username' => 'child',
                        'text' => 'Reply text',
                        'like_count' => 0,
                        'timestamp' => '2026-01-15T11:00:00+0000',
                    ],
                ],
            ],
        ],
    ]);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['replies'])->toHaveCount(1)
        ->and($normalized[0]['replies'][0]['username'])->toBe('child')
        ->and($normalized[0]['replies'][0]['text'])->toBe('Reply text');
});
