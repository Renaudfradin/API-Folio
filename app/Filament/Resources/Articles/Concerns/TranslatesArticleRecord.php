<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;

trait TranslatesArticleRecord
{
    public function getRecord(): Model
    {
        $record = parent::getRecord();

        if ($record instanceof Article && filled($this->activeLocale)) {
            $record->setLocale($this->activeLocale);
        }

        return $record;
    }
}
