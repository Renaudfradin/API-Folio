<?php

namespace Tests\Unit\LinkedIn;

use App\Models\LinkedinProfileStat;
use App\Models\User;
use App\Services\LinkedIn\LinkedInSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkedInSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_linkedin_connection_and_updates_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $service = app(LinkedInSyncService::class);

        $service->persistConnection($user, null, [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
            'scope' => 'openid profile email',
        ], [
            'sub' => 'linkedin-user-123',
            'name' => 'LinkedIn Name',
            'profile' => 'https://www.linkedin.com/in/linkedin-name',
            'picture' => 'https://example.com/avatar.jpg',
            'headline' => 'Developer',
        ]);

        $user = $user->fresh();
        $connection = $user->linkedinConnection;

        $this->assertNotNull($connection);
        $this->assertSame('linkedin-user-123', $connection->provider_user_id);
        $this->assertSame('access-token', $connection->access_token);
        $this->assertSame('refresh-token', $connection->refresh_token);
        $this->assertSame(['openid', 'profile', 'email'], $connection->scopes);
        $this->assertSame('LinkedIn Name', $connection->profile_name);
        $this->assertSame('https://www.linkedin.com/in/linkedin-name', $connection->profile_url);
        $this->assertSame('LinkedIn Name', $user->name);
        $this->assertSame('linkedin-user-123', $user->linkedin_profile_id);
        $this->assertSame('https://www.linkedin.com/in/linkedin-name', $user->linkedin_url);
        $this->assertSame('Developer', $user->linkedin_headline);
        $this->assertNotNull($user->linkedin_synced_at);
    }

    public function test_it_associates_linkedin_profile_stats_with_a_user(): void
    {
        $user = User::factory()->create();

        $stat = LinkedinProfileStat::create([
            'user_id' => $user->id,
            'metric_key' => 'profile_views',
            'metric_label' => 'Profile views',
            'value' => 42,
            'value_text' => null,
            'period_start' => now()->subMonth()->toDateString(),
            'period_end' => now()->toDateString(),
            'source' => 'linkedin',
            'payload' => ['raw' => true],
            'synced_at' => now(),
        ]);

        $this->assertTrue($stat->user()->exists());
        $this->assertSame($user->id, $stat->user->id);
        $this->assertCount(1, $user->linkedinProfileStats);
    }
}
