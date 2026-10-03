<?php

use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    config(['rate_limiting.api.limit' => 3]);
});

it('returns 429 when the api rate limit is exceeded', function () {
    RateLimiter::clear('api');

    for ($i = 0; $i < 3; $i++) {
        $this->getJson('/api/health')->assertOk();
    }

    $this->getJson('/api/health')
        ->assertStatus(429)
        ->assertJson([
            'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
        ])
        ->assertHeader('Retry-After');
});
