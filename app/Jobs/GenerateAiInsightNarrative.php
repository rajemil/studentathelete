<?php

namespace App\Jobs;

use App\Models\Insight;
use App\Services\AI\Contracts\AiClient;
use App\Services\AI\Providers\NullAiClient;
use App\Services\AI\Support\PromptBuilder;
use App\Services\Insights\InsightsService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class GenerateAiInsightNarrative implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $organizationId,
        public ?int $userId,
    ) {}

    public function handle(AiClient $ai, InsightsService $insights): void
    {
        if ($ai instanceof NullAiClient) {
            return;
        }

        if (! $this->allowLlmCall()) {
            return;
        }

        $facts = $this->loadInsightFacts();
        if ($facts === []) {
            return;
        }

        $system = <<<'PROMPT'
You summarize athletic performance insights for coaches and students.
Respond with a single JSON object only (no markdown) with exactly these keys:
- "narrative": string, 2-4 sentences in plain language summarizing the themes in the facts
- "actionable": string, one short imperative sentence the athlete or coach can do this week

Do not invent medical diagnoses. Stay grounded in the facts provided.
PROMPT;

        $userPrompt = PromptBuilder::insightNarrativeUserPrompt($this->organizationId, $this->userId, $facts);

        $result = $ai->generateStructuredJson($system, $userPrompt, ['narrative', 'actionable']);

        $aiMeta = [
            'provider' => $result->provider,
            'model' => $result->model,
            'latency_ms' => $result->latencyMs,
            'success' => $result->success,
            'error' => $result->error,
        ];

        if (! $result->success || ! is_array($result->data)) {
            activity()
                ->withProperties([
                    'organization_id' => $this->organizationId,
                    'user_id' => $this->userId,
                    'fallback_used' => true,
                    'ai' => $aiMeta,
                ])
                ->log('ai_insight_narrative_generated');

            return;
        }

        $narrative = trim((string) ($result->data['narrative'] ?? ''));
        $actionable = trim((string) ($result->data['actionable'] ?? ''));
        if ($narrative === '' || $actionable === '') {
            return;
        }

        $insights->persistNarrativeSummary(
            $this->organizationId,
            $this->userId,
            $narrative,
            $actionable,
            $aiMeta,
            CarbonImmutable::now(),
        );

        activity()
            ->withProperties([
                'organization_id' => $this->organizationId,
                'user_id' => $this->userId,
                'fallback_used' => false,
                'ai' => $aiMeta,
            ])
            ->log('ai_insight_narrative_generated');
    }

    /**
     * @return list<array{type: string, severity: string, title: string, message: string}>
     */
    private function loadInsightFacts(): array
    {
        $q = Insight::query()
            ->where('type', '!=', 'narrative_summary')
            ->where(function ($outer) {
                $outer->where('organization_id', $this->organizationId)
                    ->orWhereHas('user', fn ($u) => $u->where('organization_id', $this->organizationId));
            })
            ->orderByDesc('computed_at')
            ->limit(10);

        if ($this->userId !== null) {
            $q->where('user_id', $this->userId);
        }

        return $q->get(['type', 'severity', 'title', 'message'])
            ->map(fn (Insight $i) => [
                'type' => (string) $i->type,
                'severity' => (string) $i->severity,
                'title' => (string) $i->title,
                'message' => (string) $i->message,
            ])
            ->all();
    }

    private function allowLlmCall(): bool
    {
        $key = 'ai-llm-insights:org:'.$this->organizationId;

        return RateLimiter::attempt($key, 30, fn () => true, 60);
    }
}
