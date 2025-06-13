<?php

namespace App\Listeners\User;

use App\Events\User\UserUpdatedPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserUpdatedPasswordNotification
{
    public function handle(UserUpdatedPassword $event): void
    {
        $user = $event->user;

        try {
            Mail::to($user)->send(new \App\Mail\User\UserUpdatedPassword($user));
        } catch (Throwable $e) {
            Log::error($e);
        }
    }
}
