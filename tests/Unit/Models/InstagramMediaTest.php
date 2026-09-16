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

it('computes engagement rate from account followers', function (): void {
    $account = new InstagramAccount(['followers_count' => 1000]);
    $media = new InstagramMedia([
        'like_count' => 100,
        'comments_count' => 50,
    ]);
    $media->setRelation('account', $account);

    expect($media->engagement_rate)->toBe(15.0);
});

it('returns null engagement rate without followers', function (): void {
    $media = new InstagramMedia([
        'like_count' => 10,
        'comments_count' => 5,
    ]);
    $media->setRelation('account', new InstagramAccount(['followers_count' => 0]));

    expect($media->engagement_rate)->toBeNull();
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
