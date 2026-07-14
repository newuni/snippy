<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('draft-creation', fn (Request $request): Limit => Limit::perHour(20)
            ->by($request->ip()));

        RateLimiter::for('managed-write', fn (Request $request): Limit => Limit::perMinute(60)
            ->by($request->ip().'|'.$request->path()));

        RateLimiter::for('paste-unlock', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip().'|'.$request->path()));

        RateLimiter::for('public-aggregate', fn (Request $request): Limit => Limit::perMinute(30)
            ->by($request->ip()));

        $appUrl = (string) config('app.url');
        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);
        }

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
