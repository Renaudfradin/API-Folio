<?php

use App\Services\Instagram\InstagramGraphService;
use Illuminate\Support\Facades\Http;

it('posts a comment reply to the graph api', function (): void {
    Http::fake([
        'https://graph.instagram.com/*' => Http::response(['id' => '17873440459141029'], 200),
    ]);

    $service = app(InstagramGraphService::class);

    $result = $service->postCommentReply('17870913679156914', 'Merci !', 'test-token');

    expect($result)->toBe(['id' => '17873440459141029']);

    Http::assertSent(function ($request): bool {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/17870913679156914/replies')
            && $request['message'] === 'Merci !'
            && $request['access_token'] === 'test-token';
    });
});

it('falls back to views-only media insights when full metrics fail', function (): void {
    Http::fake([
        'https://graph.instagram.com/*/insights*' => function ($request) {
            if (str_contains($request->url(), 'metric=views,shares,saved,follows')) {
                return Http::response([], 400);
            }

            return Http::response([
                'data' => [
                    ['name' => 'views', 'values' => [['value' => 42]]],
                ],
            ], 200);
        },
    ]);

    $service = app(InstagramGraphService::class);

    $payload = $service->getMediaInsights('media123', 'test-token');

    expect($payload['data'][0]['values'][0]['value'])->toBe(42);
});

it('falls back to views-only story insights when full metrics fail', function (): void {
    Http::fake([
        'https://graph.instagram.com/*/insights*' => function ($request) {
            if (str_contains($request->url(), 'metric=views,reach,replies,likes')) {
                return Http::response([], 400);
            }

            return Http::response([
                'data' => [
                    ['name' => 'views', 'values' => [['value' => 99]]],
                ],
            ], 200);
        },
    ]);

    $service = app(InstagramGraphService::class);

    $payload = $service->getStoryInsights('story123', 'test-token');

    expect($payload['data'][0]['values'][0]['value'])->toBe(99);
});

it('loads stories with media fields from the stories edge', function (): void {
    Http::fake([
        'https://graph.instagram.com/*/stories*' => Http::response([
            'data' => [
                [
                    'id' => 'story-1',
                    'media_type' => 'IMAGE',
                    'media_url' => 'https://example.com/story.jpg',
                    'timestamp' => '2026-09-17T11:28:00+0000',
                ],
            ],
        ], 200),
    ]);

    $service = app(InstagramGraphService::class);

    $stories = $service->getStories('ig-user', 'test-token');

    expect($stories)->toHaveCount(1)
        ->and($stories[0]['id'])->toBe('story-1')
        ->and($stories[0]['media_url'])->toBe('https://example.com/story.jpg')
        ->and($stories[0]['media_product_type'])->toBe('STORY');
});

it('falls back to facebook graph when instagram stories edge is empty', function (): void {
    Http::fake([
        'https://graph.instagram.com/*/stories*' => Http::response(['data' => []], 200),
        'https://graph.facebook.com/*/stories*' => Http::response([
            'data' => [
                ['id' => 'story-fb-1'],
            ],
        ], 200),
        'https://graph.instagram.com/story-fb-1*' => Http::response([], 404),
        'https://graph.facebook.com/story-fb-1*' => Http::response([
            'id' => 'story-fb-1',
            'media_type' => 'IMAGE',
            'media_url' => 'https://example.com/fb-story.jpg',
            'timestamp' => '2026-09-17T11:28:00+0000',
        ], 200),
    ]);

    $service = app(InstagramGraphService::class);

    $stories = $service->getStories('ig-user', 'test-token');

    expect($stories)->toHaveCount(1)
        ->and($stories[0]['id'])->toBe('story-fb-1')
        ->and($stories[0]['media_product_type'])->toBe('STORY');
});
