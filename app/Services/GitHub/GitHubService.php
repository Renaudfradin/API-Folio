<?php

namespace App\Services\GitHub;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubService
{
    public function getAuthenticatedUser(): array
    {
        return Cache::remember(
            $this->cacheKey('user'),
            $this->cacheTtl(),
            fn (): array => $this->client()->get('/user')->throw()->json(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRepositories(): array
    {
        return Cache::remember(
            $this->cacheKey('repos'),
            $this->cacheTtl(),
            function (): array {
                $repos = [];
                $page = 1;

                do {
                    $response = $this->client()
                        ->get('/user/repos', [
                            'per_page' => 100,
                            'page' => $page,
                            'affiliation' => 'owner',
                            'sort' => 'updated',
                        ])
                        ->throw()
                        ->json();

                    $repos = array_merge($repos, $response);
                    $page++;
                } while (count($response) === 100);

                return $repos;
            },
        );
    }

    /**
     * @return array{
     *     public_repos: int,
     *     total_stars: int,
     *     followers: int,
     *     following: int,
     *     name: string|null,
     *     login: string,
     *     bio: string|null,
     *     html_url: string,
     *     avatar_url: string,
     *     languages: array<string, int>
     * }
     */
    public function getAccountStats(): array
    {
        return Cache::remember(
            $this->cacheKey('stats'),
            $this->cacheTtl(),
            function (): array {
                $user = $this->getAuthenticatedUser();
                $repos = $this->getRepositories();

                $languages = [];
                $totalStars = 0;

                foreach ($repos as $repo) {
                    $totalStars += (int) ($repo['stargazers_count'] ?? 0);

                    $language = $repo['language'] ?? null;
                    if (filled($language)) {
                        $languages[$language] = ($languages[$language] ?? 0) + 1;
                    }
                }

                arsort($languages);

                return [
                    'public_repos' => (int) ($user['public_repos'] ?? count($repos)),
                    'total_stars' => $totalStars,
                    'followers' => (int) ($user['followers'] ?? 0),
                    'following' => (int) ($user['following'] ?? 0),
                    'name' => $user['name'] ?? null,
                    'login' => $user['login'] ?? $this->username(),
                    'bio' => $user['bio'] ?? null,
                    'html_url' => $user['html_url'] ?? '',
                    'avatar_url' => $user['avatar_url'] ?? '',
                    'languages' => $languages,
                ];
            },
        );
    }

    /**
     * @return array{content: string, sha: string, path: string, html_url: string|null}
     */
    public function getProfileReadme(): array
    {
        $username = $this->username();

        try {
            $file = $this->client()
                ->get("/repos/{$username}/{$username}/contents/README.md")
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            if ($exception->response?->status() === 404) {
                throw new RuntimeException(
                    "Le README du profil est introuvable. Vérifie que le dépôt {$username}/{$username} existe et contient un README.md.",
                    previous: $exception,
                );
            }

            throw $this->wrapRequestException($exception);
        }

        $encoded = $file['content'] ?? '';
        $content = base64_decode(str_replace("\n", '', $encoded), true);

        if ($content === false) {
            throw new RuntimeException('Impossible de décoder le contenu du README.md.');
        }

        return [
            'content' => $content,
            'sha' => $file['sha'] ?? '',
            'path' => $file['path'] ?? 'README.md',
            'html_url' => $file['html_url'] ?? null,
        ];
    }

    /**
     * @return array{content: string, sha: string, html_url: string|null}
     */
    public function updateProfileReadme(string $content, string $sha, ?string $message = null): array
    {
        $username = $this->username();

        try {
            $response = $this->client()
                ->put("/repos/{$username}/{$username}/contents/README.md", [
                    'message' => $message ?? 'Update profile README via Folio admin',
                    'content' => base64_encode($content),
                    'sha' => $sha,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw $this->wrapRequestException($exception);
        }

        $this->forgetCache();

        $file = $response['content'] ?? [];

        return [
            'content' => $content,
            'sha' => $file['sha'] ?? $sha,
            'html_url' => $file['html_url'] ?? ($response['commit']['html_url'] ?? null),
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget($this->cacheKey('user'));
        Cache::forget($this->cacheKey('repos'));
        Cache::forget($this->cacheKey('stats'));
    }

    public function isConfigured(): bool
    {
        return filled(config('services.github.token'))
            && filled(config('services.github.username'));
    }

    protected function client(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'GitHub n’est pas configuré. Définis GITHUB_TOKEN et GITHUB_USERNAME dans ton fichier .env.',
            );
        }

        return Http::baseUrl(rtrim((string) config('services.github.base_url'), '/'))
            ->withToken((string) config('services.github.token'))
            ->accept('application/vnd.github+json')
            ->withHeaders([
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->timeout(30);
    }

    protected function username(): string
    {
        $username = config('services.github.username');

        if (blank($username)) {
            throw new RuntimeException('GITHUB_USERNAME n’est pas défini.');
        }

        return (string) $username;
    }

    protected function cacheTtl(): int
    {
        return (int) config('services.github.cache_ttl', 600);
    }

    protected function cacheKey(string $suffix): string
    {
        return 'github.'.$this->username().'.'.$suffix;
    }

    protected function wrapRequestException(RequestException $exception): RuntimeException
    {
        $status = $exception->response?->status();
        $message = $exception->response?->json('message') ?? $exception->getMessage();

        if ($status === 401) {
            return new RuntimeException(
                'Authentification GitHub échouée. Vérifie ton GITHUB_TOKEN.',
                previous: $exception,
            );
        }

        if ($status === 403) {
            return new RuntimeException(
                'Accès GitHub refusé (permissions ou rate limit). Message : '.$message,
                previous: $exception,
            );
        }

        return new RuntimeException(
            'Erreur API GitHub'.($status ? " ({$status})" : '').' : '.$message,
            previous: $exception,
        );
    }
}
