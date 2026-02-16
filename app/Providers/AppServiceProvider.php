<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Force HTTPS en production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
