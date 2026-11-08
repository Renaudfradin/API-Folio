<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Models\Article;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());
});

it('opens article pages on french', function () {
    $article = Article::factory()->create([
        'title' => ['fr' => 'Page FR', 'en' => 'Page EN'],
    ]);

    Livewire::test(ListArticles::class)
        ->assertSet('activeLocale', 'fr')
        ->assertSee('Page FR');

    Livewire::test(CreateArticle::class)
        ->assertSet('activeLocale', 'fr')
        ->assertSee('Langue');

    Livewire::test(ViewArticle::class, ['record' => $article->getKey()])
        ->assertSet('activeLocale', 'fr')
        ->assertSee('Page FR')
        ->set('activeLocale', 'en')
        ->assertSee('Page EN');
});

it('keeps each article language when switching the editor locale', function () {
    $article = Article::factory()->create([
        'active' => true,
        'slug' => 'minolta-x700',
        'title' => [
            'fr' => 'Minolta FR',
            'en' => 'Minolta EN',
        ],
        'content' => [
            'fr' => [
                'block-fr' => ['type' => 'paragraph', 'data' => ['content' => '<p>Bonjour</p>']],
            ],
            'en' => [
                'block-en' => ['type' => 'paragraph', 'data' => ['content' => '<p>Hello</p>']],
            ],
        ],
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->assertSet('activeLocale', 'fr')
        ->assertSet('data.title', 'Minolta FR')
        ->set('activeLocale', 'en')
        ->assertSet('data.title', 'Minolta EN')
        ->set('data.title', 'Minolta English')
        ->call('save')
        ->assertHasNoFormErrors();

    $article->refresh();

    $paragraph = function (mixed $content): string {
        $block = collect(is_array($content) ? $content : [])->first(
            fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'paragraph',
        );

        $html = is_array($block) ? ($block['data']['content'] ?? null) : null;

        return is_string($html) ? $html : '';
    };

    expect($article->getTranslation('title', 'fr', false))->toBe('Minolta FR')
        ->and($article->getTranslation('title', 'en', false))->toBe('Minolta English')
        ->and($paragraph($article->getTranslation('content', 'fr', false)))->toContain('Bonjour')
        ->and($paragraph($article->getTranslation('content', 'en', false)))->toContain('Hello')
        ->and($article->slug)->toBe('minolta-x700');
});

it('keeps the french blocks when another language is still empty', function () {
    $article = Article::factory()->create([
        'active' => true,
        'slug' => 'minolta-x700',
        'title' => ['fr' => 'Minolta FR'],
        'content' => [
            'fr' => [
                'block-heading' => [
                    'type' => 'heading',
                    'data' => ['content' => 'Le boîtier', 'level' => 'h2'],
                ],
                'block-fr' => [
                    'type' => 'paragraph',
                    'data' => ['content' => '<p>Bonjour</p>'],
                ],
            ],
        ],
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->assertSee('Traduire')
        ->set('activeLocale', 'de')
        ->assertSet('data.title', 'Minolta FR')
        ->assertSet('copiedLocales.de', true)
        ->tap(function ($component): void {
            $content = json_encode($component->get('data.content'), JSON_UNESCAPED_UNICODE);

            expect($content)->toContain('Bonjour')
                ->and($content)->toContain('Le boîtier');
        })
        ->set('activeLocale', 'fr')
        ->call('save')
        ->assertHasNoFormErrors();

    $article->refresh();

    expect($article->getTranslations('title'))->not->toHaveKey('de')
        ->and($article->getTranslations('content'))->not->toHaveKey('de')
        ->and($article->getTranslation('title', 'fr', false))->toBe('Minolta FR');
});

it('translates the french article into the other languages', function () {
    config([
        'services.cursor.api_key' => 'cursor_test',
        'services.cursor.base_url' => 'https://api.cursor.com',
        'services.cursor.translation_model' => 'gpt-5.4-nano',
        'services.cursor.translation_timeout' => 5,
        'services.cursor.poll_interval' => 0,
    ]);

    $translations = [];

    foreach (['en', 'de', 'es', 'ru', 'ky'] as $locale) {
        $translations[$locale] = [
            'title' => '['.$locale.'] Minolta FR',
            'content' => [
                [
                    'type' => 'heading',
                    'data' => ['content' => '['.$locale.'] Le boîtier', 'level' => 'h2'],
                ],
                [
                    'type' => 'paragraph',
                    'data' => ['content' => '<p>['.$locale.'] Bonjour</p>'],
                ],
            ],
        ];
    }

    Http::fake(function ($request) use ($translations) {
        $path = (string) parse_url($request->url(), PHP_URL_PATH);

        if ($request->method() === 'POST' && $path === '/v1/agents') {
            return Http::response([
                'agent' => ['id' => 'bc-test', 'url' => 'https://cursor.com/agents/bc-test'],
                'run' => ['id' => 'run-test'],
            ], 201);
        }

        if ($request->method() === 'GET') {
            return Http::response([
                'status' => 'FINISHED',
                'result' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);
        }

        return Http::response([], 204);
    });

    $article = Article::factory()->create([
        'active' => true,
        'slug' => 'minolta-x700',
        'title' => ['fr' => 'Minolta FR'],
        'content' => [
            'fr' => [
                'block-heading' => [
                    'type' => 'heading',
                    'data' => ['content' => 'Le boîtier', 'level' => 'h2'],
                ],
                'block-fr' => [
                    'type' => 'paragraph',
                    'data' => ['content' => '<p>Bonjour</p>'],
                ],
            ],
        ],
    ]);

    $paragraph = function (mixed $content): string {
        $block = collect(is_array($content) ? $content : [])->first(
            fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'paragraph',
        );

        $html = is_array($block) ? ($block['data']['content'] ?? null) : null;

        return is_string($html) ? $html : '';
    };

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->call('translateArticleFromFrench')
        ->assertSet('translationAgentId', 'bc-test')
        ->assertSet('activeLocale', 'fr')
        ->assertSet('data.title', 'Minolta FR')
        ->assertNotified('Traduction lancée')
        ->call('pollArticleTranslation')
        ->assertSet('translationAgentId', null)
        ->assertSet('activeLocale', 'en')
        ->assertSet('data.title', '[en] Minolta FR')
        ->assertNotified('Traduction réussie')
        ->call('save')
        ->assertHasNoFormErrors();

    $article->refresh();

    expect($article->getTranslation('title', 'fr', false))->toBe('Minolta FR')
        ->and($article->getTranslation('title', 'en', false))->toBe('[en] Minolta FR')
        ->and($article->getTranslation('title', 'ky', false))->toBe('[ky] Minolta FR')
        ->and($paragraph($article->getTranslation('content', 'fr', false)))->toContain('Bonjour')
        ->and($paragraph($article->getTranslation('content', 'en', false)))->toContain('[en]')
        ->and($paragraph($article->getTranslation('content', 'en', false)))->toContain('Bonjour')
        ->and(json_encode($article->getTranslation('content', 'en', false), JSON_UNESCAPED_UNICODE))->toContain('[en] Le boîtier')
        ->and(json_encode($article->getTranslation('content', 'en', false)))->toContain('h2')
        ->and($article->slug)->toBe('minolta-x700');
});

it('finds an article when the title search ignores case', function () {
    $zenit = Article::factory()->create([
        'title' => ['fr' => 'Zenit 12'],
    ]);
    $other = Article::factory()->create([
        'title' => ['fr' => 'Minolta'],
    ]);

    Livewire::test(ListArticles::class)
        ->searchTable('zenit')
        ->assertCanSeeTableRecords([$zenit])
        ->assertCanNotSeeTableRecords([$other])
        ->searchTable('ZENIT')
        ->assertCanSeeTableRecords([$zenit])
        ->assertCanNotSeeTableRecords([$other]);
});
