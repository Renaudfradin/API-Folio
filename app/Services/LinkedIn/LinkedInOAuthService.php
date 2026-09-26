<?php

namespace App\Services\LinkedIn;

class LinkedInOAuthService
{
    private const AUTHORIZATION_ENDPOINT = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * @return string|null Message d’erreur en français si la config OAuth est invalide.
     */
    public function oauthConfigurationError(): ?string
    {
        if (! filled(config('services.linkedin.client_id')) || ! filled(config('services.linkedin.client_secret'))) {
            return 'LinkedIn OAuth non configuré : renseignez LINKEDIN_CLIENT_ID et LINKEDIN_CLIENT_SECRET.';
        }

        if (! filled($this->redirectUri())) {
            return 'LINKEDIN_REDIRECT_URI est manquant.';
        }

        return null;
    }

    public function buildAuthorizationUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.linkedin.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
            'scope' => $this->scopeString(),
        ]);

        return self::AUTHORIZATION_ENDPOINT.'?'.$query;
    }

    public function redirectUri(): string
    {
        return config('services.linkedin.redirect') ?: route('linkedin.oauth.callback');
    }

    public function scopeString(): string
    {
        $scopes = config('services.linkedin.scopes', ['openid', 'profile', 'email']);

        if (is_string($scopes)) {
            return trim($scopes);
        }

        return collect($scopes)->filter()->implode(' ');
    }

    /**
     * @return list<string>
     */
    public function configuredScopes(): array
    {
        $scopes = config('services.linkedin.scopes', ['openid', 'profile', 'email']);

        if (is_string($scopes)) {
            return array_values(array_filter(preg_split('/[\s,]+/', $scopes) ?: []));
        }

        return array_values(array_filter($scopes));
    }
}
