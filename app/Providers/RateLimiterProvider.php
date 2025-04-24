<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class RateLimiterProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('aaa', function (Request $request) {
            return Limit::perMinute(1)->by('aaa|' . $request->ip(). '|' . $request->userAgent());
        });

        RateLimiter::for('bbb', function (Request $request) {
            return Limit::perMinute(2)->by('bbb|' . $request->ip(). '|' . $request->userAgent());
        });

        RateLimiter::for('ccc', function (Request $request) {
            return Limit::perMinute(3)->by('ccc|' . $request->ip(). '|' . $request->userAgent());
        });

        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinute(10)->by('register|' . $request->ip(). '|' . $request->userAgent());
        });     
    }
}
