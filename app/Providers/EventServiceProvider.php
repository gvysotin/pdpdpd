<?php

namespace App\Providers;


use App\Events\Registration\UserRegistered;
use App\Listeners\Registration\SendWelcomeEmailListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        UserRegistered::class => [
            SendWelcomeEmailListener::class,
        ],
    ];    

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
        //
    }
}
