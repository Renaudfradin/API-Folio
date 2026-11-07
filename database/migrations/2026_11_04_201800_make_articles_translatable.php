<?php

use App\Support\ArticleTranslations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHUNK_SIZE = 100;

    public function up(): void
    {
        if (! Schema::hasTable('articles') || Schema::hasColumn('articles', 'title_translations')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->json('title_translations')->nullable();
        });

        DB::table('articles')
            ->select(['id', 'title', 'content'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($articles): void {
                foreach ($articles as $article) {
                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update([
                            'title_translations' => $this->encode(ArticleTranslations::wrapTitle($article->title)),
                            'content' => $this->encode(ArticleTranslations::wrapContent($article->content)),
                        ]);
                }
            });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('title');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('title_translations', 'title');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('articles') || ! Schema::hasColumn('articles', 'title')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_plain')->nullable();
        });

        DB::table('articles')
            ->select(['id', 'title', 'content'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($articles): void {
                foreach ($articles as $article) {
                    $title = ArticleTranslations::wrapTitle($article->title);
                    $content = ArticleTranslations::decode($article->content);
                    $blocks = is_array($content) && ArticleTranslations::isLocaleMap($content)
                        ? ($content['fr'] ?? [])
                        : (is_array($content) ? $content : []);

                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update([
                            'title_plain' => (string) ($title['fr'] ?? ''),
                            'content' => $this->encode(is_array($blocks) ? $blocks : []),
                        ]);
                }
            });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('title');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('title_plain', 'title');
        });
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function encode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
};
