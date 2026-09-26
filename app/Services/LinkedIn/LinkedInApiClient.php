<?php

namespace App\Services\LinkedIn;

use App\Models\LinkedinConnection;
use App\Models\LinkedinPost;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LinkedInApiClient
{
    public function fetchMemberFollowersCount(LinkedinConnection $connection): ?int
    {
        $personUrn = $this->personUrn($connection);

        if ($personUrn === null) {
            return null;
        }

        try {
            $response = $this->request($connection)
                ->get('https://api.linkedin.com/rest/memberFollowersCount', [
                    'q' => 'me',
                ]);
        } catch (RequestException $exception) {
            if ($exception->response?->status() === 403 || $exception->response?->status() === 404) {
                return null;
            }

            throw $exception;
        }

        $count = data_get($response->json(), 'elements.0.memberFollowersCount');

        return is_numeric($count) ? (int) $count : null;
    }

    public function syncMemberPosts(LinkedinConnection $connection): void
    {
        $personUrn = $this->personUrn($connection);

        if ($personUrn === null) {
            return;
        }

        try {
            $response = $this->request($connection)
                ->get('https://api.linkedin.com/rest/posts', [
                    'author' => $personUrn,
                    'q' => 'author',
                    'count' => 50,
                    'sortBy' => 'LAST_MODIFIED',
                ]);
        } catch (RequestException $exception) {
            if (in_array($exception->response?->status(), [403, 404], true)) {
                return;
            }

            throw $exception;
        }

        $elements = $response->json('elements') ?? [];

        foreach ($elements as $element) {
            if (! is_array($element)) {
                continue;
            }

            $postUrn = (string) ($element['id'] ?? $element['urn'] ?? '');

            if ($postUrn === '') {
                continue;
            }

            $analytics = $this->fetchPostAnalytics($connection, $postUrn);

            LinkedinPost::query()->updateOrCreate(
                ['post_urn' => $postUrn],
                [
                    'linkedin_connection_id' => $connection->id,
                    'text' => $this->extractPostText($element),
                    'permalink' => data_get($element, 'permalink'),
                    'media_type' => data_get($element, 'content.media.id') ?? data_get($element, 'lifecycleState'),
                    'published_at' => $this->extractPublishedAt($element),
                    'impressions' => (int) ($analytics['impressions'] ?? 0),
                    'reactions' => (int) ($analytics['reactions'] ?? 0),
                    'comments' => (int) ($analytics['comments'] ?? 0),
                    'shares' => (int) ($analytics['shares'] ?? 0),
                    'clicks' => (int) ($analytics['clicks'] ?? 0),
                    'reach' => (int) ($analytics['reach'] ?? 0),
                    'video_views' => (int) ($analytics['video_views'] ?? 0),
                    'engagement_rate' => $analytics['engagement_rate'] ?? null,
                    'raw_payload' => $element,
                    'synced_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array<string, int|float|null>
     */
    protected function fetchPostAnalytics(LinkedinConnection $connection, string $postUrn): array
    {
        try {
            $response = $this->request($connection)
                ->get('https://api.linkedin.com/rest/memberCreatorPostAnalytics', [
                    'q' => 'entity',
                    'entity' => $postUrn,
                ]);
        } catch (RequestException $exception) {
            if (in_array($exception->response?->status(), [403, 404], true)) {
                return [];
            }

            throw $exception;
        }

        $elements = $response->json('elements') ?? [];
        $metrics = [
            'impressions' => 0,
            'reactions' => 0,
            'comments' => 0,
            'shares' => 0,
            'clicks' => 0,
            'reach' => 0,
            'video_views' => 0,
            'engagement_rate' => null,
        ];

        foreach ($elements as $element) {
            if (! is_array($element)) {
                continue;
            }

            $type = (string) ($element['type'] ?? $element['metricType'] ?? '');
            $value = (int) ($element['value'] ?? $element['count'] ?? 0);

            match (true) {
                str_contains(strtoupper($type), 'IMPRESSION') => $metrics['impressions'] = $value,
                str_contains(strtoupper($type), 'REACTION') => $metrics['reactions'] = $value,
                str_contains(strtoupper($type), 'COMMENT') => $metrics['comments'] = $value,
                str_contains(strtoupper($type), 'SHARE') => $metrics['shares'] = $value,
                str_contains(strtoupper($type), 'CLICK') => $metrics['clicks'] = $value,
                str_contains(strtoupper($type), 'REACH') => $metrics['reach'] = $value,
                str_contains(strtoupper($type), 'VIDEO') => $metrics['video_views'] = $value,
                default => null,
            };
        }

        if ($metrics['impressions'] > 0) {
            $engagement = $metrics['reactions'] + $metrics['comments'] + $metrics['shares'];
            $metrics['engagement_rate'] = round(($engagement / $metrics['impressions']) * 100, 2);
        }

        return $metrics;
    }

    protected function request(LinkedinConnection $connection): \Illuminate\Http\Client\PendingRequest
    {
        $token = $connection->access_token;

        if (! filled($token)) {
            throw new RuntimeException('Le token LinkedIn est manquant.');
        }

        return Http::withToken($token)
            ->acceptJson()
            ->withHeaders([
                'Linkedin-Version' => (string) config('services.linkedin.api_version', '202405'),
                'X-Restli-Protocol-Version' => '2.0.0',
            ]);
    }

    protected function personUrn(LinkedinConnection $connection): ?string
    {
        $providerUserId = $connection->provider_user_id;

        if (! filled($providerUserId)) {
            return null;
        }

        if (str_starts_with($providerUserId, 'urn:li:person:')) {
            return $providerUserId;
        }

        return 'urn:li:person:'.$providerUserId;
    }

    /**
     * @param  array<string, mixed>  $element
     */
    protected function extractPostText(array $element): ?string
    {
        $commentary = data_get($element, 'commentary');

        if (is_string($commentary)) {
            return $commentary;
        }

        if (is_array($commentary)) {
            return data_get($commentary, 'text')
                ?? data_get($commentary, 'attributes.0.text');
        }

        return data_get($element, 'specificContent.com.linkedin.ugc.ShareContent.shareCommentary.text');
    }

    /**
     * @param  array<string, mixed>  $element
     */
    protected function extractPublishedAt(array $element): ?\Illuminate\Support\Carbon
    {
        $timestamp = data_get($element, 'createdAt')
            ?? data_get($element, 'publishedAt')
            ?? data_get($element, 'firstPublishedAt');

        if ($timestamp === null) {
            return null;
        }

        if (is_numeric($timestamp)) {
            $seconds = (int) $timestamp;

            if ($seconds > 1_000_000_000_000) {
                $seconds = (int) floor($seconds / 1000);
            }

            return \Illuminate\Support\Carbon::createFromTimestamp($seconds);
        }

        return \Illuminate\Support\Carbon::parse((string) $timestamp);
    }
}
