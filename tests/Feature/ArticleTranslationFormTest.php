<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Models\Article;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
