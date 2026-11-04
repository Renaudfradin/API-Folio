<?php

namespace App\Services\Articles\Translation;

use RuntimeException;

class ArticleTranslationManager
{
    public function driver(?string $provider = null): ArticleTranslationDriver
    {
        $name = $provider ?: (string) config('translation.provider', 'cursor');
        $providers = config('translation.providers');

        if (! is_array($providers) || ! isset($providers[$name]) || ! is_array($providers[$name])) {
            $known = is_array($providers) ? implode(', ', array_keys($providers)) : '';

            throw new RuntimeException('Le connecteur « '.$name.' » n’existe pas. Services disponibles : '.$known.'.');
        }

        if (($providers[$name]['protocol'] ?? null) === 'cursor') {
            return app(CursorArticleTranslationDriver::class);
        }

        return new ChatArticleTranslationDriver($providers[$name]);
    }

    public function label(?string $provider = null): string
    {
        return $this->driver($provider)->label();
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        $providers = config('translation.providers');
        $options = [];

        if (! is_array($providers)) {
            return $options;
        }

        foreach ($providers as $name => $config) {
            if (! is_string($name) || ! is_array($config)) {
                continue;
            }

            $options[$name] = (string) ($config['label'] ?? $name);
        }

        return $options;
    }
}
