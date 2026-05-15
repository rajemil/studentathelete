<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class StudentWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainAccessCode,
    ) {}

    public function envelope(): Envelope
    {
        $org = $this->user->organization?->name;

        return new Envelope(
            subject: $org ? 'Your '.$org.' student account' : 'Your student account',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.student-welcome',
        );
    }
}
