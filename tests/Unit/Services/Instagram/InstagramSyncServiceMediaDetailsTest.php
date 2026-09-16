<?php

use App\Services\Instagram\InstagramSyncService;

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
