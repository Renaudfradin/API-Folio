<?php

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use App\Services\Instagram\InstagramStoryImportService;
use App\Services\Instagram\InstagramSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports story rows from csv and merges on media id', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::query()->create([
        'user_id' => $user->id,
        'business_account_id' => 'biz-1',
        'username' => 'renaud_photographer',
        'access_token' => 'token',
        'is_active' => true,
    ]);

    $path = tempnam(sys_get_temp_dir(), 'stories-import-');
    file_put_contents($path, implode("\n", [
        'published_at;views;reach;replies;reactions;media_id',
        '2026-09-17 13:28;99;55;0;6;story-meta-1',
        '2026-09-18 09:00;10;8;1;2;',
    ]));

    $service = app(InstagramStoryImportService::class);

    $first = $service->importFromPath($account, $path);

    expect($first['imported'])->toBe(2)
        ->and($first['updated'])->toBe(0);

    $story = InstagramMedia::query()->where('media_id', 'story-meta-1')->first();

    expect($story)->not->toBeNull()
        ->and($story->media_product_type)->toBe('STORY')
        ->and($story->view_count)->toBe(99)
        ->and($story->insight('reach'))->toBe(55)
        ->and($story->insight('reactions'))->toBe(6);

    file_put_contents($path, implode("\n", [
        'published_at;views;reach;replies;reactions;media_id',
        '2026-09-17 13:28;120;60;1;7;story-meta-1',
    ]));

    $second = $service->importFromPath($account, $path);

    expect($second['imported'])->toBe(0)
        ->and($second['updated'])->toBe(1)
        ->and($story->fresh()->view_count)->toBe(120);

    @unlink($path);
});

it('upserts story insights from webhook payload', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::query()->create([
        'user_id' => $user->id,
        'business_account_id' => 'biz-webhook',
        'username' => 'story_user',
        'access_token' => 'token',
        'is_active' => true,
    ]);

    $service = app(InstagramSyncService::class);

    $service->upsertWebhookStoryInsights($account, [
        'media_id' => 'webhook-story-1',
        'views' => 80,
        'reach' => 40,
        'replies' => 2,
        'likes' => 5,
        'timestamp' => '2026-09-20T10:00:00+0000',
    ]);

    $media = InstagramMedia::query()->where('media_id', 'webhook-story-1')->first();

    expect($media)->not->toBeNull()
        ->and($media->media_product_type)->toBe('STORY')
        ->and($media->view_count)->toBe(80)
        ->and($media->insight('reach'))->toBe(40)
        ->and($media->insight('reactions'))->toBe(5)
        ->and($media->storyRepliesCount())->toBe(2);
});
