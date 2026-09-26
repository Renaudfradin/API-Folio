<?php

namespace Tests\Unit\LinkedIn;

use App\Models\LinkedinConnection;
use App\Models\User;
use App\Services\LinkedIn\LinkedInApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkedInApiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ignores_forbidden_post_sync_without_throwing(): void
    {
        Http::fake([
            'api.linkedin.com/rest/posts*' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $user = User::factory()->create();
        $connection = LinkedinConnection::query()->create([
            'user_id' => $user->id,
            'provider_user_id' => 'abc123',
            'access_token' => 'token',
        ]);

        app(LinkedInApiClient::class)->syncMemberPosts($connection);

        $this->assertDatabaseCount('linkedin_posts', 0);
    }
}
