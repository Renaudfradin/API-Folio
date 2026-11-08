<?php

namespace App\Services\Articles\Translation;

interface ArticleTranslationDriver
{
    public function label(): string;

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array<string, array{title: string, content: array<mixed>}>
     */
    public function translate(array $french): array;

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array{agentId: string, runId: string, url: string, ready?: array<string, array{title: string, content: array<mixed>}>}
     */
    public function start(array $french): array;

    /**
     * @return array<string, array{title: string, content: array<mixed>}>|null
     */
    public function check(string $agentId, string $runId): ?array;

    public function forget(string $agentId): void;
}
