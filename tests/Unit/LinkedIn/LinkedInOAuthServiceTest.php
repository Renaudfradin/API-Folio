<?php

namespace Tests\Unit\LinkedIn;

use App\Services\LinkedIn\LinkedInOAuthService;
use Tests\TestCase;

class LinkedInOAuthServiceTest extends TestCase
{
    public function test_it_builds_authorization_url_with_space_separated_scopes(): void
    {
        config([
            'services.linkedin.client_id' => 'client-id',
            'services.linkedin.redirect' => 'https://example.test/linkedin/callback',
            'services.linkedin.scopes' => ['openid', 'profile', 'w_member_social'],
        ]);

        $service = app(LinkedInOAuthService::class);
        $url = $service->buildAuthorizationUrl('state-token');

        $this->assertStringContainsString('client_id=client-id', $url);
        $this->assertStringContainsString('scope=openid+profile+w_member_social', $url);
        $this->assertStringContainsString('state=state-token', $url);
    }

    public function test_it_reports_missing_configuration(): void
    {
        config([
            'services.linkedin.client_id' => null,
            'services.linkedin.client_secret' => null,
        ]);

        $service = app(LinkedInOAuthService::class);

        $this->assertNotNull($service->oauthConfigurationError());
    }
}
