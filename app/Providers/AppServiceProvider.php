<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Force HTTPS en production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.api.limit', 60))
                ->by($request->ip())
                ->response($this->tooManyRequestsResponse(...));
        });

        RateLimiter::for('strict', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.strict.limit', 20))
                ->by($request->ip())
                ->response($this->tooManyRequestsResponse(...));
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.login.limit', 5))
                ->by($request->ip())
                ->response($this->tooManyRequestsResponse(...));
        });
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    protected function tooManyRequestsResponse(Request $request, array $headers): \Symfony\Component\HttpFoundation\Response
    {
        return response()->json([
            'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
        ], 429, $headers);
    }
}
