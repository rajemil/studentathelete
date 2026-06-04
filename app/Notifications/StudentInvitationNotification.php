<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $invitationToken,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $orgName = Organization::query()->find($notifiable->organization_id)?->name ?? 'your school';
        $url = route('register.student', ['token' => $this->invitationToken]);

        return (new MailMessage)
            ->subject('Student Athlete invitation — '.$orgName)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been invited to join the Student Athlete Information Management System for **'.$orgName.'**.')
            ->action('Complete registration', $url)
            ->line('Use the link above to confirm your profile and sign in. This invitation link is unique to your account.')
            ->line('For security, do not share this link. If you did not expect this email, contact your administrator.');
    }
}
