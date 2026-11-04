<?php

use App\Services\Articles\ArticleContentTranslator;
use App\Services\Articles\Translation\ArticleTranslationManager;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('lists the translation connectors', function () {
    $options = app(ArticleTranslationManager::class)->options();

    expect(array_keys($options))->toBe([
        'cursor',
        'openai',
        'deepseek',
        'anthropic',
        'gemini',
        'mistral',
        'groq',
        'azure',
        'openrouter',
        'ollama',
    ]);
});

it('translates through an openai compatible connector', function (string $provider, string $url) {
    config([
        'translation.provider' => $provider,
        "translation.providers.{$provider}.key" => 'test-key',
        "translation.providers.{$provider}.url" => $url,
        "translation.providers.{$provider}.model" => 'test-model',
    ]);

    Http::fake(function ($request) use ($url) {
        expect($request->url())->toStartWith($url);

        return Http::response([
            'choices' => [
                ['message' => ['content' => json_encode(connectorTranslations(), JSON_UNESCAPED_UNICODE)]],
            ],
        ]);
    });

    $translated = app(ArticleContentTranslator::class)->translate([
        'title' => 'Minolta FR',
        'content' => [
            'heading' => [
                'type' => 'heading',
                'data' => ['content' => 'Le boîtier', 'level' => 'h2'],
            ],
            'image' => [
                'type' => 'image',
                'data' => ['url' => 'articles/minolta.jpg', 'alt' => 'Boîtier noir'],
            ],
        ],
    ], 'en');

    expect($translated['title'])->toBe('[en] Minolta FR')
        ->and($translated['content']['heading']['data']['level'])->toBe('h2')
        ->and($translated['content']['image']['data']['url'])->toBe('articles/minolta.jpg')
        ->and($translated['content']['image']['data']['alt'])->toBe('[en] Boîtier noir');
})->with([
    'openai' => ['openai', 'https://api.openai.com/v1/chat/completions'],
    'deepseek' => ['deepseek', 'https://api.deepseek.com/chat/completions'],
]);

it('asks for the provider key before calling it', function () {
    config([
        'translation.provider' => 'openai',
        'translation.providers.openai.key' => null,
    ]);

    expect(fn () => app(ArticleContentTranslator::class)->translate([
        'title' => 'Bonjour',
        'content' => [],
    ], 'en'))->toThrow(RuntimeException::class, 'OPENAI_API_KEY');
});

/**
 * @return array<string, array{title: string, content: array<mixed>}>
 */
function connectorTranslations(): array
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
                'image' => [
                    'type' => 'image',
                    'data' => ['url' => 'https://evil.example/changed.jpg', 'alt' => '['.$locale.'] Boîtier noir'],
                ],
            ],
        ];
    }

    return $translations;
}
