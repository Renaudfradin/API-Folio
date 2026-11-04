<?php

namespace App\Services\Articles\Translation;

use App\Services\Articles\CursorTranslationAgent;

class CursorArticleTranslationDriver implements ArticleTranslationDriver
{
    public function __construct(private CursorTranslationAgent $agent) {}

    public function label(): string
    {
        return 'Cursor';
    }

    public function translate(array $french): array
    {
        return $this->agent->translate($french);
    }

    public function start(array $french): array
    {
        return $this->agent->start($french);
    }

    public function check(string $agentId, string $runId): ?array
    {
        return $this->agent->check($agentId, $runId);
    }

    public function forget(string $agentId): void
    {
        $this->agent->forget($agentId);
    }
}
