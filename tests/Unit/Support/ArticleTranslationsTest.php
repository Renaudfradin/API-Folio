<?php

use App\Support\ArticleTranslations;

it('wraps a plain article title and builder content under french', function () {
    expect(ArticleTranslations::wrapTitle('Minolta x700'))->toBe([
        'fr' => 'Minolta x700',
    ]);

    $blocks = [
        'block-1' => [
            'type' => 'paragraph',
            'data' => ['content' => '<p>Bonjour</p>'],
        ],
    ];

    expect(ArticleTranslations::wrapContent($blocks))->toBe([
        'fr' => $blocks,
    ]);
});

it('keeps article translations that are already keyed by locale', function () {
    $title = ['fr' => 'Bonjour', 'en' => 'Hello'];
    $content = ['de' => []];

    expect(ArticleTranslations::wrapTitle(json_encode($title)))->toBe($title)
        ->and(ArticleTranslations::wrapContent($content))->toBe($content);
});
