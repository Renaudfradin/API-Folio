<?php

namespace App\Services\Articles\Translation;

use App\Models\Article;
use RuntimeException;

class TranslationReply
{
    /**
     * @return array<string, array{title: string, content: array<mixed>}>
     */
    public static function decode(string $reply): array
    {
        $decoded = self::decodeJson($reply);
        $translations = [];

        foreach (Article::LOCALES as $locale) {
            if ($locale === Article::DEFAULT_LOCALE) {
                continue;
            }

            $localeData = $decoded[$locale] ?? null;

            if (! is_array($localeData)) {
                throw new RuntimeException('La réponse ne contient pas la langue '.$locale.'.');
            }

            $translations[$locale] = [
                'title' => is_string($localeData['title'] ?? null) ? $localeData['title'] : '',
                'content' => is_array($localeData['content'] ?? null) ? $localeData['content'] : [],
            ];
        }

        return $translations;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeJson(string $reply): array
    {
        $reply = trim($reply);

        if (preg_match('/```(?:json)?\s*(.*?)```/s', $reply, $matches) === 1) {
            $reply = trim($matches[1]);
        }

        $decoded = json_decode($reply, true);

        if (! is_array($decoded)) {
            $start = strpos($reply, '{');
            $end = strrpos($reply, '}');

            if ($start !== false && $end !== false && $end >= $start) {
                $decoded = json_decode(substr($reply, $start, $end - $start + 1), true);
            }
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('La réponse n’est pas un JSON exploitable.');
        }

        return $decoded;
    }
}
