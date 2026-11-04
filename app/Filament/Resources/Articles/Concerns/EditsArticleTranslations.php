<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Models\Article;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;

trait EditsArticleTranslations
{
    #[Locked]
    public array $otherLocaleData = [];

    public ?string $oldActiveLocale = null;

    public function updatingActiveLocale(): void
    {
        $this->oldActiveLocale = $this->activeLocale;
    }

    public function updatedActiveLocale(): void
    {
        if (blank($this->oldActiveLocale) || $this->oldActiveLocale === $this->activeLocale) {
            return;
        }

        if (! in_array($this->activeLocale, Article::LOCALES, true)) {
            $this->activeLocale = $this->oldActiveLocale;

            return;
        }

        $this->resetValidation();

        $this->otherLocaleData[$this->oldActiveLocale] = Arr::only(
            $this->form->getStateSnapshot(),
            Article::TRANSLATABLE_ATTRIBUTES,
        );

        $incoming = $this->otherLocaleData[$this->activeLocale] ?? $this->blankTranslation();
        unset($this->otherLocaleData[$this->activeLocale]);

        $this->form->fill([
            ...Arr::except($this->data ?? [], Article::TRANSLATABLE_ATTRIBUTES),
            ...$incoming,
        ]);
    }

    protected function fillForm(): void
    {
        if (! $this instanceof EditRecord) {
            parent::fillForm();

            return;
        }

        $record = $this->getRecord();
        $data = $record->attributesToArray();

        foreach (Article::LOCALES as $locale) {
            $localeData = [];

            foreach (Article::TRANSLATABLE_ATTRIBUTES as $attribute) {
                $localeData[$attribute] = $this->normalizeTranslationValue(
                    $attribute,
                    $record->getTranslation($attribute, $locale, false),
                );
            }

            if ($locale === $this->activeLocale) {
                $data = [
                    ...$data,
                    ...$localeData,
                ];

                continue;
            }

            $this->otherLocaleData[$locale] = $localeData;
        }

        $this->form->fill($this->mutateFormDataBeforeFill($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = $this->persistArticleTranslations(new ($this->getModel()), $data);

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->persistArticleTranslations($record, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persistArticleTranslations(Model $record, array $data): Model
    {
        $record->fill(Arr::except($data, Article::TRANSLATABLE_ATTRIBUTES));

        $locales = [
            $this->activeLocale => Arr::only($data, Article::TRANSLATABLE_ATTRIBUTES),
        ];

        foreach ($this->otherLocaleData as $locale => $localeData) {
            if (! is_string($locale) || ! in_array($locale, Article::LOCALES, true)) {
                continue;
            }

            $locales[$locale] = Arr::only($localeData, Article::TRANSLATABLE_ATTRIBUTES);
        }

        foreach ($locales as $locale => $localeData) {
            $this->writeArticleLocale($record, $locale, $localeData);
        }

        $record->save();

        return $record;
    }

    /**
     * @param  array<string, mixed>  $localeData
     */
    private function writeArticleLocale(Model $record, string $locale, array $localeData): void
    {
        $title = $localeData['title'] ?? '';
        $content = $localeData['content'] ?? [];

        if (! is_string($title)) {
            $title = '';
        }

        if (! is_array($content)) {
            $content = [];
        }

        if (! filled($title) && ! filled($content)) {
            if ($record->exists && method_exists($record, 'forgetTranslation')) {
                $record->forgetTranslation('title', $locale);
                $record->forgetTranslation('content', $locale);
            }

            return;
        }

        $record->setTranslation('title', $locale, $title);
        $record->setTranslation('content', $locale, $content);
    }

    private function normalizeTranslationValue(string $attribute, mixed $value): mixed
    {
        if ($attribute === 'content') {
            return is_array($value) ? $value : [];
        }

        return is_string($value) ? $value : '';
    }

    /**
     * @return array{title: string, content: array<mixed>}
     */
    private function blankTranslation(): array
    {
        return [
            'title' => '',
            'content' => [],
        ];
    }
}
