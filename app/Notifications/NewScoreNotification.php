<?php

namespace App\Notifications;

use App\Models\PerformanceScore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewScoreNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PerformanceScore $score)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'new_score',
            'title' => 'New score recorded',
            'message' => sprintf(
                '%s: %s (%s)',
                $this->score->sport?->name ?? 'Sport',
                number_format((float) $this->score->score, 1),
                $this->score->category
            ),
            'score_id' => $this->score->id,
            'sport_id' => $this->score->sport_id,
            'scored_on' => $this->score->scored_on?->toDateString(),
        ];
    }
}

