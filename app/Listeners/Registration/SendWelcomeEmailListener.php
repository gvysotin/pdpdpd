<?php

namespace App\Listeners\Registration;

use App\Events\Registration\UserRegistered;
use App\Jobs\Registration\SendWelcomeEmailJob;

class SendWelcomeEmailListener
{
    public function handle(UserRegistered $event): void
    {
        SendWelcomeEmailJob::dispatch($event->user);
    }
}