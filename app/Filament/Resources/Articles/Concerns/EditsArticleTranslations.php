<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Models\Article;
use App\Services\Articles\ArticleContentTranslator;
use App\Services\Articles\Translation\ArticleTranslationManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Throwable;

trait EditsArticleTranslations
{
    #[Locked]
    public array $otherLocaleData = [];

    /**
     * Locales whose form was filled from the French blocks and not edited yet.
     *
     * @var array<string, true>
     */
    #[Locked]
    public array $copiedLocales = [];

    public ?string $oldActiveLocale = null;

    #[Locked]
    public ?string $translationProvider = null;

    #[Locked]
    public ?string $translationAgentId = null;

    #[Locked]
    public ?string $translationRunId = null;

    #[Locked]
    public ?string $translationAgentUrl = null;

    #[Locked]
    public bool $translationProgressNotified = false;

    /**
     * @var array{title?: string, content?: array<mixed>}
     */
    #[Locked]
    public array $translationSource = [];

    public function translateArticleAction(): Action
    {
        return Action::make('translateArticle')
            ->label('Traduire')
            ->icon(Heroicon::OutlinedLanguage)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Traduire depuis le français')
            ->modalDescription('Choisis le service. Le texte français lui est envoyé avec le même prompt. Reste sur cette page : la version anglaise s’affiche dès que la réponse arrive. Les images et les fichiers sont conservés. Les traductions déjà enregistrées sont remplacées.')
            ->schema([
                Select::make('provider')
                    ->label('Service')
                    ->options(fn (): array => app(ArticleTranslationManager::class)->options())
                    ->default(fn (): string => (string) config('translation.provider', 'cursor'))
                    ->required()
                    ->native(false),
            ])
            ->modalSubmitActionLabel('Traduire')
            ->action(function (array $data): void {
                $provider = $data['provider'] ?? null;

                $this->translateArticleFromFrench(is_string($provider) ? $provider : null);
            });
    }

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

        $snapshot = Arr::only(
            $this->form->getStateSnapshot(),
            Article::TRANSLATABLE_ATTRIBUTES,
        );

        $this->storeLocaleSnapshot($this->oldActiveLocale, $snapshot);

        $incoming = $this->otherLocaleData[$this->activeLocale] ?? null;
        unset($this->otherLocaleData[$this->activeLocale]);

        $french = $this->otherLocaleData[Article::DEFAULT_LOCALE] ?? null;

        if ($this->translationIsEmpty($incoming) && $this->activeLocale !== Article::DEFAULT_LOCALE) {
            if (is_array($french) && ! $this->translationIsEmpty($french)) {
                $incoming = $this->copyTranslation($french);
                $this->copiedLocales[$this->activeLocale] = true;
            }
        } elseif (
            $this->activeLocale !== Article::DEFAULT_LOCALE
            && is_array($incoming)
            && ! empty($this->copiedLocales[$this->activeLocale])
            && $this->sameTranslation($incoming, is_array($french) ? $french : null)
        ) {
            $this->copiedLocales[$this->activeLocale] = true;
        } else {
            unset($this->copiedLocales[$this->activeLocale]);
        }

        $this->form->fill([
            ...Arr::except($this->data ?? [], Article::TRANSLATABLE_ATTRIBUTES),
            ...($incoming ?? $this->blankTranslation()),
        ]);
    }

    public function translateArticleFromFrench(?string $provider = null): void
    {
        $snapshot = Arr::only(
            $this->form->getStateSnapshot(),
            Article::TRANSLATABLE_ATTRIBUTES,
        );
        $this->otherLocaleData[$this->activeLocale] = $snapshot;

        $french = $this->activeLocale === Article::DEFAULT_LOCALE
            ? $snapshot
            : ($this->otherLocaleData[Article::DEFAULT_LOCALE] ?? null);

        if (! is_array($french) || $this->translationIsEmpty($french)) {
            unset($this->otherLocaleData[$this->activeLocale]);

            $this->notifyTranslation(
                'warning',
                'Article en français manquant',
                'Écris et enregistre le titre et le contenu en français avant de traduire.',
            );

            return;
        }

        if (filled($this->translationAgentId)) {
            $this->notifyTranslation(
                'warning',
                'Traduction déjà en cours',
                'Reste sur cette page. La version anglaise s’affichera dès que l’agent aura répondu.',
            );
            $this->scheduleTranslationPoll();

            return;
        }

        $provider = $provider ?: (string) config('translation.provider', 'cursor');

        try {
            $started = app(ArticleContentTranslator::class)->begin($french, $provider);
        } catch (Throwable $exception) {
            report($exception);
            unset($this->otherLocaleData[$this->activeLocale]);

            $this->notifyTranslation('danger', 'Traduction échouée', $exception->getMessage());

            return;
        }

        $this->translationProvider = $provider;
        $this->translationSource = $french;
        $label = app(ArticleTranslationManager::class)->label($provider);

        if (is_array($started['ready'] ?? null)) {
            $this->showTranslations(app(ArticleContentTranslator::class)->apply($french, $started['ready']));
            $this->clearTranslationAgent();

            return;
        }

        $this->translationAgentId = $started['agentId'];
        $this->translationRunId = $started['runId'];
        $this->translationAgentUrl = $started['url'];

        $this->notifyTranslation(
            'info',
            'Traduction lancée',
            $label.' traduit l’article. Reste sur cette page : le résultat s’affichera dans le formulaire.',
        );

        $this->scheduleTranslationPoll();
    }

    public function pollArticleTranslation(): void
    {
        if (blank($this->translationAgentId) || blank($this->translationRunId)) {
            return;
        }

        try {
            $translations = app(ArticleContentTranslator::class)->collect(
                $this->translationAgentId,
                $this->translationRunId,
                $this->translationSource,
                $this->translationProvider,
            );
        } catch (Throwable $exception) {
            report($exception);
            $body = $exception->getMessage();

            if (filled($this->translationAgentUrl)) {
                $body .= ' '.$this->translationAgentUrl;
            }

            $this->clearTranslationAgent();

            $this->notifyTranslation('danger', 'Traduction échouée', $body);

            return;
        }

        if ($translations === null) {
            if (! $this->translationProgressNotified) {
                $this->translationProgressNotified = true;
                $this->notifyTranslation(
                    'info',
                    'Traduction en cours',
                    app(ArticleTranslationManager::class)->label($this->translationProvider).' travaille encore. Reste sur cette page.',
                );
            }

            $this->scheduleTranslationPoll();

            return;
        }

        $this->showTranslations($translations);
        $this->clearTranslationAgent();
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

        $french = $locales[Article::DEFAULT_LOCALE] ?? null;

        foreach ($locales as $locale => $localeData) {
            if (
                $locale !== Article::DEFAULT_LOCALE
                && ! empty($this->copiedLocales[$locale])
                && $this->sameTranslation($localeData, is_array($french) ? $french : null)
            ) {
                if ($record->exists && method_exists($record, 'forgetTranslation')) {
                    $record->forgetTranslation('title', $locale);
                    $record->forgetTranslation('content', $locale);
                }

                continue;
            }

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

    /**
     * @param  array<string, array{title: string, content: array<mixed>}>  $translations
     */
    private function showTranslations(array $translations): void
    {
        $french = $this->translationSource;
        $done = [];

        $this->otherLocaleData[Article::DEFAULT_LOCALE] = Arr::only($french, Article::TRANSLATABLE_ATTRIBUTES);

        foreach (Article::LOCALES as $locale) {
            if ($locale === Article::DEFAULT_LOCALE) {
                continue;
            }

            $translated = $translations[$locale] ?? null;

            if (! is_array($translated)) {
                continue;
            }

            $this->otherLocaleData[$locale] = $translated;
            unset($this->copiedLocales[$locale]);
            $done[] = Article::LOCALE_LABELS[$locale];
        }

        $visibleLocale = isset($translations['en']) ? 'en' : array_key_first($translations);
        $visible = is_string($visibleLocale) ? ($translations[$visibleLocale] ?? null) : null;

        if (is_string($visibleLocale) && is_array($visible)) {
            unset($this->otherLocaleData[$visibleLocale]);
            $this->activeLocale = $visibleLocale;
            $this->form->fill([
                ...Arr::except($this->data ?? [], Article::TRANSLATABLE_ATTRIBUTES),
                ...$visible,
            ]);
        }

        $this->resetValidation();

        $missing = [];

        foreach (Article::LOCALES as $locale) {
            if ($locale === Article::DEFAULT_LOCALE || isset($translations[$locale])) {
                continue;
            }

            $missing[] = Article::LOCALE_LABELS[$locale];
        }

        if ($done === []) {
            $this->notifyTranslation('danger', 'Traduction échouée', 'L’agent n’a renvoyé aucune langue.');

            return;
        }

        $visibleLabel = Article::LOCALE_LABELS[$this->activeLocale] ?? $this->activeLocale;

        if ($missing === []) {
            $this->notifyTranslation(
                'success',
                'Traduction réussie',
                'La version '.$visibleLabel.' est affichée. Les autres langues sont dans le menu Langue : '.implode(', ', $done).'.',
            );

            return;
        }

        $this->notifyTranslation(
            'warning',
            'Traduction partielle',
            'Prêtes : '.implode(', ', $done).'. À reprendre : '.implode(', ', $missing).'.',
        );
    }

    private function notifyTranslation(string $status, string $title, ?string $body = null): void
    {
        $notification = Notification::make()->title($title);

        if (filled($body)) {
            $notification->body($body);
        }

        match ($status) {
            'success' => $notification
                ->success()
                ->icon(Heroicon::OutlinedCheckCircle)
                ->persistent(),
            'danger' => $notification
                ->danger()
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->persistent(),
            'warning' => $notification
                ->warning()
                ->icon(Heroicon::OutlinedExclamationCircle)
                ->persistent(),
            default => $notification
                ->info()
                ->icon(Heroicon::OutlinedLanguage)
                ->seconds(8),
        };

        $notification->send();
    }

    private function clearTranslationAgent(): void
    {
        $this->translationProvider = null;
        $this->translationAgentId = null;
        $this->translationRunId = null;
        $this->translationAgentUrl = null;
        $this->translationSource = [];
        $this->translationProgressNotified = false;
    }

    private function scheduleTranslationPoll(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->js('setTimeout(() => $wire.pollArticleTranslation(), 5000)');
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function storeLocaleSnapshot(string $locale, array $snapshot): void
    {
        $french = $locale === Article::DEFAULT_LOCALE
            ? $snapshot
            : ($this->otherLocaleData[Article::DEFAULT_LOCALE] ?? null);

        if (
            $locale !== Article::DEFAULT_LOCALE
            && ! empty($this->copiedLocales[$locale])
            && $this->sameTranslation($snapshot, is_array($french) ? $french : null)
        ) {
            unset($this->otherLocaleData[$locale]);

            return;
        }

        $this->otherLocaleData[$locale] = $snapshot;
        unset($this->copiedLocales[$locale]);
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

    /**
     * @param  array<string, mixed>  $translation
     * @return array{title: string, content: array<mixed>}
     */
    private function copyTranslation(array $translation): array
    {
        $copy = json_decode(json_encode($translation), true);

        if (! is_array($copy)) {
            return $this->blankTranslation();
        }

        return [
            'title' => is_string($copy['title'] ?? null) ? $copy['title'] : '',
            'content' => is_array($copy['content'] ?? null) ? $copy['content'] : [],
        ];
    }

    private function translationIsEmpty(?array $data): bool
    {
        if ($data === null) {
            return true;
        }

        $title = $data['title'] ?? null;
        $content = $data['content'] ?? null;

        return ! filled(is_string($title) ? $title : null)
            && ! filled(is_array($content) ? $content : null);
    }

    /**
     * @param  array<string, mixed>  $localeData
     */
    private function sameTranslation(array $localeData, ?array $french): bool
    {
        if ($french === null) {
            return false;
        }

        return $this->translationFingerprint($localeData) === $this->translationFingerprint($french);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function translationFingerprint(array $data): string
    {
        $parts = [trim((string) ($data['title'] ?? ''))];
        $content = $data['content'] ?? [];

        if (! is_array($content)) {
            return implode("\n", $parts);
        }

        foreach ($content as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $payload = is_array($block['data'] ?? null) ? $block['data'] : [];

            $parts[] = match ($type) {
                'paragraph' => 'paragraph:'.$this->plainText($payload['content'] ?? ''),
                'image' => 'image:'.$this->stringifyUrl($payload['url'] ?? '').'|'.trim((string) ($payload['alt'] ?? '')),
                'file' => 'file:'.$this->stringifyUrl($payload['url'] ?? '').'|'.trim((string) ($payload['label'] ?? '')),
                default => $type.':'.trim((string) ($payload['content'] ?? '')).'|'.trim((string) ($payload['level'] ?? '')),
            };
        }

        return implode("\n", $parts);
    }

    private function plainText(mixed $html): string
    {
        $text = trim(strip_tags(is_string($html) ? $html : ''));

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function stringifyUrl(mixed $url): string
    {
        if (is_string($url)) {
            return $url;
        }

        if (! is_array($url)) {
            return '';
        }

        $first = reset($url);

        return is_string($first) ? $first : (string) json_encode($url);
    }
}
