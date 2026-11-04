<?php

namespace App\Filament\Actions;

use App\Models\Article;
use Filament\Actions\SelectAction;

class ArticleLocaleSwitcher extends SelectAction
{
    public static function getDefaultName(): ?string
    {
        return 'activeLocale';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Langue');
        $this->options(Article::LOCALE_LABELS);
    }
}
