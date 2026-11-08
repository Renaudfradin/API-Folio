<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\ArticleLocaleSwitcher;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Concerns\SelectsArticleLocale;
use App\Filament\Resources\Articles\Concerns\TranslatesArticleRecord;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArticle extends ViewRecord
{
    use SelectsArticleLocale;
    use TranslatesArticleRecord;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArticleLocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
