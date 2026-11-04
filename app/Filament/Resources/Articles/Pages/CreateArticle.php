<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\ArticleLocaleSwitcher;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Concerns\EditsArticleTranslations;
use App\Filament\Resources\Articles\Concerns\SelectsArticleLocale;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use EditsArticleTranslations;
    use SelectsArticleLocale;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArticleLocaleSwitcher::make(),
        ];
    }
}
