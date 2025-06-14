<?php

namespace App\Mail\User;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserUnverifiedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mails.user.registered.subject', ['value' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.blade.user.unverified_email',
            with: [
                'system' => MailService::getSystemData(),
                'dateDelete' => $this->user->created_at->addMonth()->format('d-m-Y'),
                'dateForceDelete' => $this->user->created_at->addMonth()->addWeeks(2)->format('d-m-Y'),
            ]
        );
    }
}
