<?php

namespace App\Providers;

use App\Models\Swipe;
use App\Observers\SwipeObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceHttps(true);
        }

        Swipe::observe(SwipeObserver::class);
    }
}
