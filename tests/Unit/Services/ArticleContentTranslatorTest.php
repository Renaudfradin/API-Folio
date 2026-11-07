<?php

use App\Services\Articles\ArticleContentTranslator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.cursor.api_key' => 'cursor_test',
        'services.cursor.base_url' => 'https://api.cursor.com',
        'services.cursor.translation_model' => 'gpt-5.4-nano',
        'services.cursor.translation_timeout' => 5,
        'services.cursor.poll_interval' => 0,
    ]);
});

it('asks for a cursor api key before starting an agent', function () {
    config(['services.cursor.api_key' => null]);

    expect(fn () => app(ArticleContentTranslator::class)->translate([
        'title' => 'Bonjour',
        'content' => [],
    ], 'en'))->toThrow(RuntimeException::class, 'CURSOR_API_KEY');
});

it('translates article text with gpt 5.4 nano and keeps files and images', function () {
    Http::fake(function ($request) {
        $path = (string) parse_url($request->url(), PHP_URL_PATH);

        if ($request->method() === 'POST' && $path === '/v1/agents') {
            expect($request['model']['id'])->toBe('gpt-5.4-nano')
                ->and($request['prompt']['text'])->toContain('Minolta FR');

            return Http::response([
                'agent' => ['id' => 'bc-test', 'url' => 'https://cursor.com/agents/bc-test'],
                'run' => ['id' => 'run-test', 'status' => 'CREATING'],
            ]);
        }

        if ($request->method() === 'GET' && str_contains($path, '/runs/')) {
            return Http::response([
                'status' => 'FINISHED',
                'result' => json_encode(translatedArticle(), JSON_UNESCAPED_UNICODE),
            ]);
        }

        return Http::response([], 204);
    });

    $translated = app(ArticleContentTranslator::class)->translate([
        'title' => 'Minolta FR',
        'content' => [
            'heading' => [
                'type' => 'heading',
                'data' => ['content' => 'Le boîtier', 'level' => 'h2'],
            ],
            'paragraph' => [
                'type' => 'paragraph',
                'data' => ['content' => '<p>Bonjour <strong>cher</strong> lecteur</p>'],
            ],
            'image' => [
                'type' => 'image',
                'data' => ['url' => 'articles/minolta.jpg', 'alt' => 'Boîtier noir'],
            ],
            'file' => [
                'type' => 'file',
                'data' => ['url' => 'articles/files/notice.pdf', 'label' => 'Notice'],
            ],
        ],
    ], 'en');

    expect($translated['title'])->toBe('[en] Minolta FR')
        ->and($translated['content']['heading']['data']['content'])->toBe('[en] Le boîtier')
        ->and($translated['content']['heading']['data']['level'])->toBe('h2')
        ->and($translated['content']['paragraph']['data']['content'])->toContain('<strong>')
        ->and($translated['content']['paragraph']['data']['content'])->toContain('[en] Bonjour')
        ->and($translated['content']['paragraph']['data']['content'])->toContain('[en] cher')
        ->and($translated['content']['image']['data']['url'])->toBe('articles/minolta.jpg')
        ->and($translated['content']['image']['data']['alt'])->toBe('[en] Boîtier noir')
        ->and($translated['content']['file']['data']['url'])->toBe('articles/files/notice.pdf')
        ->and($translated['content']['file']['data']['label'])->toBe('[en] Notice');

    Http::assertSent(fn ($request): bool => $request->method() === 'DELETE');
});

/**
 * @return array<string, array{title: string, content: array<mixed>}>
 */
function translatedArticle(): array
{
    $translations = [];

    foreach (['en', 'de', 'es', 'ru', 'ky'] as $locale) {
        $translations[$locale] = [
            'title' => '['.$locale.'] Minolta FR',
            'content' => [
                'heading' => [
                    'type' => 'heading',
                    'data' => ['content' => '['.$locale.'] Le boîtier', 'level' => 'h1'],
                ],
                'paragraph' => [
                    'type' => 'paragraph',
                    'data' => ['content' => '<p>['.$locale.'] Bonjour <strong>['.$locale.'] cher</strong> lecteur</p>'],
                ],
                'image' => [
                    'type' => 'image',
                    'data' => ['url' => 'https://evil.example/changed.jpg', 'alt' => '['.$locale.'] Boîtier noir'],
                ],
                'file' => [
                    'type' => 'file',
                    'data' => ['url' => 'https://evil.example/file.pdf', 'label' => '['.$locale.'] Notice'],
                ],
            ],
        ];
    }

    return $translations;
}
