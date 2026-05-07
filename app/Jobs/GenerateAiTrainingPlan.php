<?php

namespace App\Jobs;

use App\Models\Sport;
use App\Models\TrainingRecommendation;
use App\Models\User;
use App\Notifications\TrainingScheduleNotification;
use App\Services\AI\Contracts\AiClient;
use App\Services\AI\Support\PromptBuilder;
use App\Services\Training\TrainingRecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\AI\Providers\NullAiClient;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class GenerateAiTrainingPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $userId,
        public ?int $sportId,
        public bool $sendNotification,
        public ?int $organizationId,
    ) {}

    public function handle(TrainingRecommendationService $training, AiClient $ai): void
    {
        $athlete = User::query()->where('id', $this->userId)->where('role', 'student')->first();
        if (! $athlete) {
            return;
        }

        $sport = $this->sportId ? Sport::query()->find($this->sportId) : null;

        $rec = $training->generateWeeklyPlanHeuristic($athlete, $sport, CarbonImmutable::now());

        if ($this->sendNotification && $athlete->profile) {
            $athlete->notify(new TrainingScheduleNotification($rec));
        }

        if ($ai instanceof NullAiClient) {
            return;
        }

        if (! $this->allowLlmCall($athlete)) {
            return;
        }

        $facts = $rec->metadata ?? [];
        $facts = is_array($facts) ? $facts : [];

        $system = <<<'PROMPT'
You are a certified strength & conditioning assistant for student athletes.
You MUST respond with a single JSON object (no markdown) using exactly these keys:
- "title": string, short weekly plan title
- "body_markdown": string, markdown with sections ## Focus, ## Weekly outline (bullets per day Mon-Sun), ## Recovery, ## Cautions (if injury_risk is medium/high). Keep under 1200 words.
- "summary": string, 2 sentences max for a dashboard card

Ground your advice ONLY in the numeric facts provided. Do not invent injuries or medical diagnoses.
If fatigue_score is high, bias toward recovery. If injury_risk is high, avoid maximal intensity language.
PROMPT;

        $userPrompt = PromptBuilder::trainingPlanUserPrompt($athlete, $sport?->name, [
            'week_key' => $facts['week_key'] ?? null,
            'trend' => $facts['trend'] ?? null,
            'fatigue_score' => $facts['fatigue_score'] ?? null,
            'injury_risk' => $facts['injury_risk'] ?? null,
            'prediction' => $facts['prediction'] ?? null,
            'next_event' => $facts['next_event'] ?? null,
            'timeline' => $facts['timeline'] ?? null,
        ]);

        $result = $ai->generateStructuredJson($system, $userPrompt, ['title', 'body_markdown', 'summary']);

        $meta = is_array($rec->metadata) ? $rec->metadata : [];
        $meta['ai'] = [
            'provider' => $result->provider,
            'model' => $result->model,
            'latency_ms' => $result->latencyMs,
            'success' => $result->success,
            'error' => $result->error,
        ];

        if (! $result->success || ! is_array($result->data)) {
            $rec->metadata = $meta;
            $rec->save();

            activity()
                ->performedOn($rec)
                ->withProperties([
                    'user_id' => $athlete->id,
                    'sport_id' => $sport?->id,
                    'fallback_used' => true,
                    'ai' => $meta['ai'],
                ])
                ->log('ai_training_plan_generated');

            return;
        }

        $title = (string) ($result->data['title'] ?? $rec->title);
        $body = (string) ($result->data['body_markdown'] ?? '');
        $summary = (string) ($result->data['summary'] ?? '');
        if ($body === '') {
            $rec->metadata = $meta;
            $rec->save();

            return;
        }

        $rec->title = $title !== '' ? $title : $rec->title;
        $rec->recommendation = $body;
        $meta['ai']['summary'] = $summary;
        $rec->metadata = $meta;
        $rec->save();

        activity()
            ->performedOn($rec)
            ->withProperties([
                'user_id' => $athlete->id,
                'sport_id' => $sport?->id,
                'fallback_used' => false,
                'ai' => $meta['ai'],
            ])
            ->log('ai_training_plan_generated');
    }

    private function allowLlmCall(User $athlete): bool
    {
        $orgId = $this->organizationId ?? (int) $athlete->organization_id;
        if ($orgId <= 0) {
            return true;
        }

        $key = 'ai-llm:org:'.$orgId;

        return RateLimiter::attempt($key, 60, fn () => true, 60);
    }
}

