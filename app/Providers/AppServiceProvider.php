<?php

namespace App\Providers;

use App\Models\Swipe;
use App\Observers\SwipeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Swipe::observe(SwipeObserver::class);
    }
}
