<?php

namespace App\Support;

use App\Models\Article;

class ArticleTranslations
{
    /**
     * @return array<string, string>
     */
    public static function wrapTitle(mixed $title): array
    {
        $decoded = self::decode($title);

        if (is_array($decoded) && self::isLocaleMap($decoded)) {
            return $decoded;
        }

        return [Article::DEFAULT_LOCALE => is_scalar($title) ? (string) $title : ''];
    }

    /**
     * @return array<string, mixed>
     */
    public static function wrapContent(mixed $content): array
    {
        $decoded = self::decode($content);

        if (! is_array($decoded)) {
            return [Article::DEFAULT_LOCALE => []];
        }

        if (self::isLocaleMap($decoded)) {
            return $decoded;
        }

        return [Article::DEFAULT_LOCALE => $decoded];
    }

    /**
     * @param  array<mixed>  $value
     */
    public static function isLocaleMap(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (! is_string($key) || ! in_array($key, Article::LOCALES, true)) {
                return false;
            }
        }

        return true;
    }

    public static function decode(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        return $decoded;
    }
}
