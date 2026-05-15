<?php

namespace App\Notifications;

use App\Models\SportApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SportApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public SportApplication $application, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database']; // you can add 'mail' if needed
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sport = $this->application->sport;
        $student = $this->application->user;
        $subject = 'Your sport application has been ' . $this->status;
        return (new MailMessage)
            ->subject($subject)
            ->line('Your application for ' . $sport->name . ' has been ' . $this->status . '.')
            ->action('View Application', url('/student/sports/' . $sport->id . '/applications'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'sport_application_id' => $this->application->id,
            'sport_id' => $this->application->sport_id,
            'sport_slug' => $this->application->sport->slug,
            'sport_name' => $this->application->sport?->name,
            'student_id' => $this->application->user_id,
            'student_name' => $this->application->user?->name,
            'status' => $this->status,
            'title' => 'Application ' . ucfirst($this->status),
            'message' => 'Your application for ' . ($this->application->sport?->name ?? 'the sport') . ' has been ' . $this->status . '.',
        ];
    }
}
