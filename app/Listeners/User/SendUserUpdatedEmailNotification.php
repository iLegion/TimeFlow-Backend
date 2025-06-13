<?php

namespace App\Listeners\User;

use App\Events\User\UserUpdatedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserUpdatedEmailNotification
{
    public function handle(UserUpdatedEmail $event): void
    {
        $user = $event->user;
        $oldEmail = $event->oldEmail;

        try {
            Mail::to($oldEmail)->send(new \App\Mail\User\UserUpdatedEmail($user, $oldEmail));
        } catch (Throwable $e) {
            Log::error($e);
        }
    }
}
