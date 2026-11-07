<?php

namespace App\Models;

use App\Traits\HasActiveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    use HasActiveRouteBinding;
    use HasFactory;
    use HasTranslations;

    public const DEFAULT_LOCALE = 'fr';

    /**
     * @var list<string>
     */
    public const LOCALES = ['fr', 'en', 'de', 'es', 'ru', 'ky'];

    /**
     * @var array<string, string>
     */
    public const LOCALE_LABELS = [
        'fr' => 'Français',
        'en' => 'English',
        'de' => 'Deutsch',
        'es' => 'Español',
        'ru' => 'Русский',
        'ky' => 'Кыргызча',
    ];

    /**
     * @var list<string>
     */
    public const TRANSLATABLE_ATTRIBUTES = ['title', 'content'];

    /**
     * @var list<string>
     */
    public array $translatable = self::TRANSLATABLE_ATTRIBUTES;

    protected $fillable = [
        'title',
        'slug',
        'image',
        'content',
        'active',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function getFallbackLocale(): string
    {
        return self::DEFAULT_LOCALE;
    }

    public static function resolveLocale(mixed $locale): string
    {
        return is_string($locale) && in_array($locale, self::LOCALES, true)
            ? $locale
            : self::DEFAULT_LOCALE;
    }

    public function translationLocale(string $requested): string
    {
        $requested = self::resolveLocale($requested);

        if ($requested !== self::DEFAULT_LOCALE && ! $this->hasLocaleContent($requested)) {
            return self::DEFAULT_LOCALE;
        }

        return $requested;
    }

    public function hasLocaleContent(string $locale): bool
    {
        return filled($this->getTranslation('title', $locale, false))
            || filled($this->getTranslation('content', $locale, false));
    }

    /**
     * @return list<string>
     */
    public function availableLocales(): array
    {
        return array_values(array_filter(
            self::LOCALES,
            fn (string $locale): bool => $this->hasLocaleContent($locale),
        ));
    }

    public function translatedTitle(string $locale): string
    {
        $title = $this->getTranslation('title', $locale, false);

        return is_string($title) ? $title : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function translatedContent(string $locale): array
    {
        $content = $this->getTranslation('content', $locale, false);

        return is_array($content) ? $content : [];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function readingStats(): HasMany
    {
        return $this->hasMany(ArticleReadingStat::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
