<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Services\Articles\Translation\ArticleTranslationManager;
use InvalidArgumentException;

class ArticleContentTranslator
{
    /**
     * @var array<string, array{title: string, content: array<mixed>}>|null
     */
    private ?array $translations = null;

    public function __construct(private ArticleTranslationManager $manager) {}

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array{title: string, content: array<mixed>}
     */
    public function translate(array $french, string $locale): array
    {
        $translations = $this->translateAll($french);

        if (! isset($translations[$locale])) {
            throw new InvalidArgumentException("La langue {$locale} ne peut pas être traduite.");
        }

        return $translations[$locale];
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array<string, array{title: string, content: array<mixed>}>
     */
    public function translateAll(array $french): array
    {
        if ($this->translations !== null) {
            return $this->translations;
        }

        $source = $this->source($french);

        $translations = [];

        foreach ($this->manager->driver()->translate($source) as $locale => $translated) {
            if (! in_array($locale, Article::LOCALES, true) || $locale === Article::DEFAULT_LOCALE) {
                continue;
            }

            $translations[$locale] = $this->merge($source, $translated);
        }

        return $this->translations = $translations;
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array{agentId: string, runId: string, url: string, ready?: array<string, array{title: string, content: array<mixed>}>}
     */
    public function begin(array $french, ?string $provider = null): array
    {
        return $this->manager->driver($provider)->start($this->source($french));
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array<string, array{title: string, content: array<mixed>}>|null
     */
    public function collect(string $agentId, string $runId, array $french, ?string $provider = null): ?array
    {
        $driver = $this->manager->driver($provider);
        $raw = $driver->check($agentId, $runId);

        if ($raw === null) {
            return null;
        }

        $translations = $this->apply($french, $raw);
        $driver->forget($agentId);

        return $translations;
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @param  array<string, array{title: string, content: array<mixed>}>  $rawTranslations
     * @return array<string, array{title: string, content: array<mixed>}>
     */
    public function apply(array $french, array $rawTranslations): array
    {
        $source = $this->source($french);
        $translations = [];

        foreach ($rawTranslations as $locale => $translated) {
            if (! is_string($locale) || ! in_array($locale, Article::LOCALES, true) || $locale === Article::DEFAULT_LOCALE || ! is_array($translated)) {
                continue;
            }

            $translations[$locale] = $this->merge($source, [
                'title' => is_string($translated['title'] ?? null) ? $translated['title'] : '',
                'content' => is_array($translated['content'] ?? null) ? $translated['content'] : [],
            ]);
        }

        return $translations;
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     * @return array{title: string, content: array<mixed>}
     */
    private function source(array $french): array
    {
        return [
            'title' => (string) ($french['title'] ?? ''),
            'content' => is_array($french['content'] ?? null) ? $french['content'] : [],
        ];
    }

    /**
     * @param  array{title: string, content: array<mixed>}  $french
     * @param  array{title: string, content: array<mixed>}  $translated
     * @return array{title: string, content: array<mixed>}
     */
    private function merge(array $french, array $translated): array
    {
        $title = trim($translated['title']);

        return [
            'title' => $title !== '' ? $translated['title'] : $french['title'],
            'content' => $this->mergeBlocks($french['content'], $translated['content']),
        ];
    }

    /**
     * @param  array<mixed>  $sourceBlocks
     * @param  array<mixed>  $translatedBlocks
     * @return array<mixed>
     */
    private function mergeBlocks(array $sourceBlocks, array $translatedBlocks): array
    {
        $translatedByKey = [];
        $translatedInOrder = [];

        foreach ($translatedBlocks as $key => $block) {
            if (! is_array($block)) {
                continue;
            }

            $translatedByKey[(string) $key] = $block;
            $translatedInOrder[] = $block;
        }

        $merged = [];
        $index = 0;

        foreach ($sourceBlocks as $key => $block) {
            if (! is_array($block)) {
                $merged[$key] = $block;

                continue;
            }

            $translated = $translatedByKey[(string) $key] ?? $translatedInOrder[$index] ?? null;
            $index++;
            $merged[$key] = $this->mergeBlock($block, is_array($translated) ? $translated : null);
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>|null  $translated
     * @return array<string, mixed>
     */
    private function mergeBlock(array $source, ?array $translated): array
    {
        $data = is_array($source['data'] ?? null) ? $source['data'] : [];
        $translatedData = is_array($translated['data'] ?? null) ? $translated['data'] : [];
        $type = $source['type'] ?? null;

        if (in_array($type, ['heading', 'text', 'paragraph'], true)) {
            $content = $translatedData['content'] ?? null;

            if (is_string($content) && trim($content) !== '') {
                $data['content'] = $content;
            }
        }

        if ($type === 'image') {
            $alt = $translatedData['alt'] ?? null;

            if (is_string($alt) && trim($alt) !== '') {
                $data['alt'] = $alt;
            }
        }

        if ($type === 'file') {
            $label = $translatedData['label'] ?? null;

            if (is_string($label) && trim($label) !== '') {
                $data['label'] = $label;
            }
        }

        $source['data'] = $data;

        return $source;
    }
}
