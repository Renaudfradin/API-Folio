<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Facades\Storage;

trait TransformsArticleContent
{
    /**
     * @param  array<string, mixed>|null  $content
     * @return list<array<string, mixed>>
     */
    protected function transformContent(?array $content): array
    {
        if (blank($content)) {
            return [];
        }

        return collect($content)
            ->values()
            ->map(function (mixed $block): ?array {
                if (! is_array($block)) {
                    return null;
                }

                $type = $block['type'] ?? null;
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];

                return match ($type) {
                    'heading' => [
                        'type' => 'heading',
                        'content' => $data['content'] ?? null,
                        'level' => $data['level'] ?? null,
                    ],
                    'paragraph' => [
                        'type' => 'paragraph',
                        'content' => $data['content'] ?? null,
                    ],
                    'text' => [
                        'type' => 'text',
                        'content' => $data['content'] ?? null,
                    ],
                    'image' => [
                        'type' => 'image',
                        'url' => $this->scalewayUrl($data['url'] ?? null),
                        'alt' => $data['alt'] ?? null,
                    ],
                    'file' => [
                        'type' => 'file',
                        'url' => $this->scalewayUrl($data['url'] ?? null),
                        'label' => $data['label'] ?? null,
                    ],
                    default => null,
                };
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function scalewayUrl(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }

        if (blank($path)) {
            return null;
        }

        return Storage::disk('scaleway')->url($path);
    }
}
