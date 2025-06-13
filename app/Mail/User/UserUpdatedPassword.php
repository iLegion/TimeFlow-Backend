<?php

namespace App\Mail\User;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserUpdatedPassword extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mails.user.updated_password.subject', ['value' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.blade.user.updated_password',
            with: [
                'system' => MailService::getSystemData(),
            ]
        );
    }
}
