<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Filament\Translatable\SpatieTranslatableContentDriver;
use App\Models\Article;
use Filament\Support\Contracts\TranslatableContentDriver;

trait SelectsArticleLocale
{
    public ?string $activeLocale = Article::DEFAULT_LOCALE;

    public function getActiveSchemaLocale(): ?string
    {
        return $this->activeLocale;
    }

    public function getActiveTableLocale(): ?string
    {
        return $this->activeLocale;
    }

    /**
     * @return class-string<TranslatableContentDriver>|null
     */
    public function getFilamentTranslatableContentDriver(): ?string
    {
        return SpatieTranslatableContentDriver::class;
    }
}
