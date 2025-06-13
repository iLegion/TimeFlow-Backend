<?php

namespace App\Mail\User;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $oldEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mails.user.updated_email.subject', ['value' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.blade.user.updated_email',
            with: [
                'system' => MailService::getSystemData(),
            ]
        );
    }
}
