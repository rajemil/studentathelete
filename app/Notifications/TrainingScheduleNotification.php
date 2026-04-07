<?php

namespace App\Notifications;

use App\Models\TrainingRecommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingScheduleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly TrainingRecommendation $plan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'training_schedule',
            'title' => 'New weekly training plan',
            'message' => $this->plan->title,
            'plan_id' => $this->plan->id,
            'starts_on' => $this->plan->starts_on?->toDateString(),
            'ends_on' => $this->plan->ends_on?->toDateString(),
        ];
    }
}

