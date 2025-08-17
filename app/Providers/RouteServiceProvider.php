<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('posts', function (Request $request) {
            $key = 'post:' . optional($request->user())->id ?: $request->ip();
            return Limit::perMinute(30)->by($key);
        });
    }
}

