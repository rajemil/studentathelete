<?php

namespace App\Notifications;

use App\Models\SportApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SportApplicationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public SportApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sport = $this->application->sport;
        $student = $this->application->user;
        $q = $this->application->qualification_passed ? 'meets' : 'does not fully meet';

        return (new MailMessage)
            ->subject('New sport application: '.$sport->name)
            ->line($student->name.' applied to '.$sport->name.'.')
            ->line('Automated eligibility check: the student '.$q.' the published rules.')
            ->action('Review sport', url('/sports/'.$sport->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'sport_application_id' => $this->application->id,
            'sport_id' => $this->application->sport_id,
            'sport_name' => $this->application->sport?->name,
            'student_id' => $this->application->user_id,
            'student_name' => $this->application->user?->name,
            'qualification_passed' => $this->application->qualification_passed,
        ];
    }
}
