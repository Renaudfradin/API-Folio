<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\ArticleLocaleSwitcher;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Concerns\SelectsArticleLocale;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    use SelectsArticleLocale;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArticleLocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
