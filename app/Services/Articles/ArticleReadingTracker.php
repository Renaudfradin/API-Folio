<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Models\ArticleReadingStat;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ArticleReadingTracker
{
    public const VIEW = 'view';

    public const READ = 'read';

    public const DEDUPE_HOURS = 6;

    public function recordView(Article $article, string $locale): void
    {
        $this->record($article, $locale, self::VIEW);
    }

    public function recordRead(Article $article, string $locale): void
    {
        $this->record($article, $locale, self::READ);
    }

    private function record(Article $article, string $locale, string $type): void
    {
        $locale = Article::resolveLocale($locale);
        $key = sprintf(
            'article-reading:%d:%s:%s:%s',
            $article->getKey(),
            $locale,
            $type,
            hash('sha256', (request()->ip() ?? '').'|'.(request()->userAgent() ?? '')),
        );

        if (! Cache::add($key, true, now()->addHours(self::DEDUPE_HOURS))) {
            return;
        }

        try {
            $stat = ArticleReadingStat::query()->firstOrCreate(
                [
                    'article_id' => $article->getKey(),
                    'locale' => $locale,
                ],
                [
                    'views_count' => 0,
                    'reads_count' => 0,
                ],
            );

            if ($type === self::READ) {
                $stat->increment('reads_count', 1, [
                    'last_read_at' => now(),
                    'updated_at' => now(),
                ]);

                return;
            }

            $stat->increment('views_count', 1, [
                'last_viewed_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Cache::forget($key);

            throw $exception;
        }
    }
}
