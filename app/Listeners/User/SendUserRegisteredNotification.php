<?php

namespace App\Listeners\User;

use App\Events\User\UserRegistered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserRegisteredNotification
{
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        try {
            Mail::to($user)->send(new \App\Mail\User\UserRegistered($user));
        } catch (Throwable $e) {
            Log::error($e);
        }
    }
}
