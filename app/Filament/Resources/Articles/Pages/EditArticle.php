<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\ArticleLocaleSwitcher;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Concerns\EditsArticleTranslations;
use App\Filament\Resources\Articles\Concerns\SelectsArticleLocale;
use App\Filament\Resources\Articles\Concerns\TranslatesArticleRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    use EditsArticleTranslations;
    use SelectsArticleLocale;
    use TranslatesArticleRecord;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArticleLocaleSwitcher::make(),
            $this->translateArticleAction(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
