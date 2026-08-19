<?php

namespace App\Services\Google;

use Carbon\CarbonImmutable;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\ApiDataRow;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class GoogleSearchConsoleService
{
    /**
     * @return array{
     *     property: string,
     *     start_date: string,
     *     end_date: string,
     *     clicks: int,
     *     impressions: int,
     *     ctr: float,
     *     position: float
     * }
     */
    public function getSiteStats(string $siteKey, int $days = 28): array
    {
        return Cache::remember(
            $this->cacheKey($siteKey, 'stats', $days),
            $this->cacheTtl(),
            function () use ($siteKey, $days): array {
                try {
                    [$startDate, $endDate] = $this->resolveDateRange($days);
                    $property = $this->property($siteKey);

                    $request = new SearchAnalyticsQueryRequest();
                    $request->setStartDate($startDate);
                    $request->setEndDate($endDate);
                    $request->setType(SearchAnalyticsQueryRequest::TYPE_WEB);
                    $request->setAggregationType(SearchAnalyticsQueryRequest::AGGREGATION_TYPE_BY_PROPERTY);
                    $request->setRowLimit(1);

                    $rows = $this->searchConsole()
                        ->searchanalytics
                        ->query($property, $request)
                        ->getRows() ?? [];

                    $row = $rows[0] ?? null;

                    return [
                        'property' => $property,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'clicks' => (int) round($row?->getClicks() ?? 0),
                        'impressions' => (int) round($row?->getImpressions() ?? 0),
                        'ctr' => (float) ($row?->getCtr() ?? 0.0),
                        'position' => (float) ($row?->getPosition() ?? 0.0),
                    ];
                } catch (Throwable $exception) {
                    throw $this->wrapGoogleException($exception);
                }
            },
        );
    }

    /**
     * @return array{
     *     property: string,
     *     start_date: string,
     *     end_date: string,
     *     labels: array<int, string>,
     *     clicks: array<int, int>,
     *     impressions: array<int, int>
     * }
     */
    public function getDailySeries(string $siteKey, int $days = 28): array
    {
        return Cache::remember(
            $this->cacheKey($siteKey, 'series', $days),
            $this->cacheTtl(),
            function () use ($siteKey, $days): array {
                try {
                    [$startDate, $endDate] = $this->resolveDateRange($days);
                    $property = $this->property($siteKey);

                    $request = new SearchAnalyticsQueryRequest();
                    $request->setStartDate($startDate);
                    $request->setEndDate($endDate);
                    $request->setType(SearchAnalyticsQueryRequest::TYPE_WEB);
                    $request->setDimensions(['date']);
                    $request->setAggregationType(SearchAnalyticsQueryRequest::AGGREGATION_TYPE_BY_PROPERTY);
                    $request->setRowLimit($days);

                    $rows = $this->searchConsole()
                        ->searchanalytics
                        ->query($property, $request)
                        ->getRows() ?? [];

                    $series = collect($rows)
                        ->filter(fn (mixed $row): bool => $row instanceof ApiDataRow)
                        ->map(function (ApiDataRow $row): array {
                            $date = $row->getKeys()[0] ?? null;

                            return [
                                'label' => $date
                                    ? CarbonImmutable::parse($date)->translatedFormat('d M')
                                    : 'N/A',
                                'clicks' => (int) round($row->getClicks() ?? 0),
                                'impressions' => (int) round($row->getImpressions() ?? 0),
                            ];
                        })
                        ->values();

                    return [
                        'property' => $property,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'labels' => $series->pluck('label')->all(),
                        'clicks' => $series->pluck('clicks')->all(),
                        'impressions' => $series->pluck('impressions')->all(),
                    ];
                } catch (Throwable $exception) {
                    throw $this->wrapGoogleException($exception);
                }
            },
        );
    }

    public function forgetCache(?string $siteKey = null): void
    {
        $keys = $siteKey ? [$siteKey] : array_keys((array) config('services.search_console.sites', []));

        foreach ($keys as $key) {
            Cache::forget($this->cacheKey($key, 'stats', 28));
            Cache::forget($this->cacheKey($key, 'series', 28));
        }
    }

    public function isConfigured(?string $siteKey = null): bool
    {
        if (! $this->hasCredentialsConfig()) {
            return false;
        }

        if ($siteKey === null) {
            return true;
        }

        return filled(data_get(config('services.search_console.sites'), "{$siteKey}.property"));
    }

    public function property(string $siteKey): string
    {
        $property = data_get(config('services.search_console.sites'), "{$siteKey}.property");

        if (blank($property)) {
            throw new RuntimeException("La propriete Search Console du site [{$siteKey}] n'est pas configuree.");
        }

        return (string) $property;
    }

    protected function searchConsole(): SearchConsole
    {
        try {
            $client = new Client();
            $client->setApplicationName(config('app.name').' Search Console');
            $client->setAuthConfig($this->credentials());
            $client->setScopes([SearchConsole::WEBMASTERS_READONLY]);

            return new SearchConsole($client);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Impossible d initialiser Google Search Console. Verifie les credentials configures.',
                previous: $exception,
            );
        }
    }

    /**
     * @return array<string, mixed>|string
     */
    protected function credentials(): array|string
    {
        $credentialsPath = config('services.search_console.credentials_path');

        if (filled($credentialsPath)) {
            return (string) $credentialsPath;
        }

        $credentialsJson = config('services.search_console.credentials_json');

        if (blank($credentialsJson)) {
            throw new RuntimeException(
                'Google Search Console n est pas configure. Definis GOOGLE_SERVICE_ACCOUNT_CREDENTIALS ou GOOGLE_SERVICE_ACCOUNT_JSON.',
            );
        }

        $decodedJson = json_decode((string) $credentialsJson, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedJson)) {
            return $decodedJson;
        }

        $base64Decoded = base64_decode((string) $credentialsJson, true);

        if ($base64Decoded !== false) {
            $decodedBase64Json = json_decode($base64Decoded, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedBase64Json)) {
                return $decodedBase64Json;
            }
        }

        throw new RuntimeException('GOOGLE_SERVICE_ACCOUNT_JSON doit contenir un JSON valide ou son equivalent en base64.');
    }

    protected function hasCredentialsConfig(): bool
    {
        return filled(config('services.search_console.credentials_path'))
            || filled(config('services.search_console.credentials_json'));
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveDateRange(int $days): array
    {
        $safeDays = max($days, 1);
        $endDate = CarbonImmutable::now()->subDays(3);
        $startDate = $endDate->subDays($safeDays - 1);

        return [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ];
    }

    protected function cacheKey(string $siteKey, string $suffix, int $days): string
    {
        return "search-console.{$siteKey}.{$suffix}.{$days}";
    }

    protected function cacheTtl(): int
    {
        return (int) config('services.search_console.cache_ttl', 3600);
    }

    protected function wrapGoogleException(Throwable $exception): RuntimeException
    {
        if ($exception instanceof GoogleServiceException) {
            return new RuntimeException(
                'Erreur API Google Search Console : '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return new RuntimeException(
            'Erreur Google Search Console : '.$exception->getMessage(),
            previous: $exception,
        );
    }
}
