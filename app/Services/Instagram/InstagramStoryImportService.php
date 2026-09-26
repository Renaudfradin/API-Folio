<?php

namespace App\Services\Instagram;

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class InstagramStoryImportService
{
    /**
     * @var array<string, list<string>>
     */
    protected const COLUMN_ALIASES = [
        'published_at' => ['published_at', 'date', 'published', 'publié_le', 'publie_le', 'timestamp'],
        'views' => ['views', 'vues', 'view_count'],
        'reach' => ['reach', 'portee', 'portée'],
        'replies' => ['replies', 'reponses', 'réponses', 'reponses_story'],
        'reactions' => ['reactions', 'likes', 'reaction'],
        'media_id' => ['media_id', 'id', 'instagram_media_id'],
        'thumbnail_url' => ['thumbnail_url', 'thumbnail', 'image_url', 'aperçu', 'apercu'],
        'permalink' => ['permalink', 'url', 'lien'],
        'caption' => ['caption', 'legende', 'légende'],
    ];

    /**
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function importFromPath(InstagramAccount $account, string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Fichier CSV illisible : '.$path);
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Impossible d’ouvrir le fichier CSV.');
        }

        $headerRow = fgetcsv($handle, 0, $this->detectDelimiter($path));

        if ($headerRow === false) {
            fclose($handle);

            throw new RuntimeException('Le fichier CSV est vide.');
        }

        $columnMap = $this->mapHeaders($headerRow);

        if (! isset($columnMap['published_at'])) {
            fclose($handle);

            throw new RuntimeException('Colonne de date requise (published_at, date, publié_le…).');
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, $this->detectDelimiter($path))) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $record = $this->rowToRecord($account, $row, $columnMap);

            if ($record === null) {
                $skipped++;

                continue;
            }

            $existing = InstagramMedia::query()
                ->where('media_id', $record['media_id'])
                ->first();

            InstagramMedia::query()->updateOrCreate(
                ['media_id' => $record['media_id']],
                $record,
            );

            if ($existing === null) {
                $imported++;
            } else {
                $updated++;
            }
        }

        fclose($handle);

        return compact('imported', 'updated', 'skipped');
    }

    protected function detectDelimiter(string $path): string
    {
        $sample = (string) file_get_contents($path, false, null, 0, 2048);

        $semicolons = substr_count($sample, ';');
        $commas = substr_count($sample, ',');

        return $semicolons > $commas ? ';' : ',';
    }

    /**
     * @param  array<int, string|null>  $headerRow
     * @return array<string, int>
     */
    protected function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);

            foreach (self::COLUMN_ALIASES as $canonical => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$canonical] = $index;
                }
            }
        }

        return $map;
    }

    protected function normalizeHeader(string $header): string
    {
        $header = Str::ascii(mb_strtolower(trim($header)));
        $header = str_replace([' ', '-'], '_', $header);

        return $header;
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $columnMap
     * @return array<string, mixed>|null
     */
    protected function rowToRecord(InstagramAccount $account, array $row, array $columnMap): ?array
    {
        $publishedAtRaw = $this->cell($row, $columnMap, 'published_at');

        if (! filled($publishedAtRaw)) {
            return null;
        }

        try {
            $timestamp = Carbon::parse($publishedAtRaw);
        } catch (\Throwable) {
            return null;
        }

        $views = (int) $this->cell($row, $columnMap, 'views', '0');
        $reach = (int) $this->cell($row, $columnMap, 'reach', '0');
        $replies = (int) $this->cell($row, $columnMap, 'replies', '0');
        $reactions = (int) $this->cell($row, $columnMap, 'reactions', '0');

        $mediaId = $this->cell($row, $columnMap, 'media_id');

        if (! filled($mediaId)) {
            $mediaId = sprintf(
                'import:story:%s:%s',
                $account->id,
                $timestamp->utc()->format('Y-m-d\TH:i:s\Z'),
            );
        }

        $insights = [
            'views' => $views,
            'reach' => $reach,
            'replies' => $replies,
            'reactions' => $reactions,
        ];

        return [
            'instagram_account_id' => $account->id,
            'media_id' => (string) $mediaId,
            'caption' => $this->cell($row, $columnMap, 'caption'),
            'permalink' => $this->cell($row, $columnMap, 'permalink'),
            'media_type' => 'IMAGE',
            'media_product_type' => 'STORY',
            'media_url' => null,
            'thumbnail_url' => $this->cell($row, $columnMap, 'thumbnail_url'),
            'like_count' => $reactions,
            'comments_count' => $replies,
            'view_count' => $views,
            'timestamp' => $timestamp,
            'insights' => $insights,
            'raw_data' => ['source' => 'import'],
            'children' => [],
            'comments' => [],
            'synced_at' => now(),
        ];
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $columnMap
     */
    protected function cell(array $row, array $columnMap, string $key, ?string $default = null): ?string
    {
        if (! isset($columnMap[$key])) {
            return $default;
        }

        $value = $row[$columnMap[$key]] ?? null;

        if ($value === null) {
            return $default;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? $default : $trimmed;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn (?string $cell): bool => ! filled(trim((string) $cell)));
    }
}
