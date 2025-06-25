<?php

namespace App\Listeners\User;

use App\Events\User\UserEmailVerificationRequested;
use App\Events\User\UserRegistered;
use App\Events\User\UserUpdatedEmail;
use App\Services\User\UserEmailVerificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserEmailVerificationNotification
{
    public function handle(UserRegistered|UserEmailVerificationRequested|UserUpdatedEmail $event): void
    {
        $user = $event->user;

        try {
            $code = UserEmailVerificationService::create($user->email);

            Mail::to($user)->send(new \App\Mail\User\UserEmailVerification($user, $code));
        } catch (Throwable $e) {
            Log::error($e);
        }
    }
}
