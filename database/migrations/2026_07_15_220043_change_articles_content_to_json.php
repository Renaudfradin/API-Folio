<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const CHUNK_SIZE = 100;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('articles') || ! Schema::hasColumn('articles', 'content')) {
            return;
        }

        DB::transaction(function (): void {
            $this->normalizeArticlesContent();

            if ($this->contentColumnIsJson()) {
                return;
            }

            $this->castContentColumnToJson();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('articles') || ! Schema::hasColumn('articles', 'content')) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('articles')
                ->select(['id', 'content'])
                ->orderBy('id')
                ->chunkById(self::CHUNK_SIZE, function ($articles): void {
                    foreach ($articles as $article) {
                        DB::table('articles')
                            ->where('id', $article->id)
                            ->update([
                                'content' => $this->contentToPlainText($article->content),
                            ]);
                    }
                });

            if (! $this->contentColumnIsJson()) {
                return;
            }

            $this->castContentColumnToText();
        });
    }

    private function normalizeArticlesContent(): void
    {
        DB::table('articles')
            ->select(['id', 'content'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($articles): void {
                foreach ($articles as $article) {
                    $normalized = $this->normalizeContent($article->content);

                    if ($normalized === (string) $article->content) {
                        continue;
                    }

                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update(['content' => $normalized]);
                }
            });
    }

    private function normalizeContent(mixed $content): string
    {
        if (blank($content)) {
            return $this->encode([]);
        }

        if (is_array($content)) {
            return $this->encode($content);
        }

        $decoded = json_decode((string) $content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->encode($decoded);
        }

        return $this->encode([
            (string) Str::uuid() => [
                'type' => 'paragraph',
                'data' => [
                    'content' => (string) $content,
                ],
            ],
        ]);
    }

    private function contentToPlainText(mixed $content): string
    {
        if (blank($content)) {
            return '';
        }

        $blocks = is_array($content)
            ? $content
            : json_decode((string) $content, true);

        if (! is_array($blocks)) {
            return (string) $content;
        }

        $parts = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = $block['type'] ?? null;
            $text = $block['data']['content'] ?? null;

            if (! in_array($type, ['paragraph', 'heading'], true) || blank($text)) {
                continue;
            }

            $parts[] = (string) $text;
        }

        return implode("\n\n", $parts);
    }

    private function contentColumnIsJson(): bool
    {
        $type = Schema::getColumnType('articles', 'content');

        return in_array($type, ['json', 'jsonb'], true);
    }

    private function castContentColumnToJson(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE articles ALTER COLUMN content TYPE jsonb USING content::jsonb');

            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->json('content')->change();
        });
    }

    private function castContentColumnToText(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE articles ALTER COLUMN content TYPE text USING content::text');

            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }

    private function encode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
};
