<?php

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;

it('returns preview url from thumbnail or media url', function (): void {
    $media = new InstagramMedia([
        'thumbnail_url' => 'https://example.com/thumb.jpg',
        'media_url' => 'https://example.com/media.jpg',
    ]);

    expect($media->preview_url)->toBe('https://example.com/thumb.jpg');

    $media->thumbnail_url = null;

    expect($media->preview_url)->toBe('https://example.com/media.jpg');
});

it('reads insight values from json', function (): void {
    $media = new InstagramMedia([
        'insights' => ['reach' => 42, 'saved' => 3],
    ]);

    expect($media->insight('reach'))->toBe(42)
        ->and($media->insight('missing', 'n/a'))->toBe('n/a');
});

it('computes story engagement rate from reach and interactions', function (): void {
    $media = new InstagramMedia([
        'media_product_type' => 'STORY',
        'like_count' => 6,
        'comments_count' => 0,
        'view_count' => 99,
        'insights' => ['reach' => 55, 'replies' => 0, 'reactions' => 6],
    ]);

    expect($media->isStory())->toBeTrue()
        ->and($media->engagement_rate)->toBe(11.0);
});

it('computes engagement rate from views when available', function (): void {
    $media = new InstagramMedia([
        'like_count' => 100,
        'comments_count' => 50,
        'view_count' => 923,
        'insights' => ['shares' => 3, 'saved' => 1],
    ]);

    expect($media->engagement_rate)->toBe(16.0);
});

it('computes engagement rate from account followers when views are zero', function (): void {
    $account = new InstagramAccount(['followers_count' => 1000]);
    $media = new InstagramMedia([
        'like_count' => 100,
        'comments_count' => 50,
        'view_count' => 0,
    ]);
    $media->setRelation('account', $account);

    expect($media->engagement_rate)->toBe(15.0);
});

it('returns null engagement rate without followers', function (): void {
    $media = new InstagramMedia([
        'like_count' => 10,
        'comments_count' => 5,
        'view_count' => 0,
    ]);
    $media->setRelation('account', new InstagramAccount(['followers_count' => 0]));

    expect($media->engagement_rate)->toBeNull();
});

it('reads insight counts for performance metrics', function (): void {
    $media = new InstagramMedia([
        'insights' => ['shares' => 3, 'saved' => 1, 'follows' => 2],
    ]);

    expect($media->insightCount('shares'))->toBe(3)
        ->and($media->insightCount('saved'))->toBe(1)
        ->and($media->insightCount('follows'))->toBe(2)
        ->and($media->insightValue('reach'))->toBeNull();
});

it('returns carousel children as all media items', function (): void {
    $media = new InstagramMedia([
        'media_id' => 'parent',
        'media_type' => 'CAROUSEL_ALBUM',
        'children' => [
            ['id' => '1', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/1.jpg', 'thumbnail_url' => null],
            ['id' => '2', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/2.jpg', 'thumbnail_url' => null],
        ],
    ]);

    expect($media->all_media_items)->toHaveCount(2);
});

it('falls back to single media item when not a carousel', function (): void {
    $media = new InstagramMedia([
        'media_id' => 'solo',
        'media_type' => 'IMAGE',
        'media_url' => 'https://example.com/solo.jpg',
    ]);

    expect($media->all_media_items)->toHaveCount(1)
        ->and($media->all_media_items[0]['media_url'])->toBe('https://example.com/solo.jpg');
});
