<?php

namespace App\Services\AI\Support;

use App\Models\User;

/**
 * Builds compact, PII-safe JSON blobs for LLM grounding (first name only, numeric facts).
 */
final class PromptBuilder
{
    public static function firstName(User $user): string
    {
        $name = trim((string) $user->name);
        $parts = preg_split('/\s+/', $name, 2);

        return $parts[0] !== '' ? $parts[0] : 'Athlete';
    }

    /**
     * @param  Collection<int, array<string, mixed>>|array<int, array<string, mixed>>  $insightFacts
     */
    public static function trainingPlanUserPrompt(User $athlete, ?string $sportName, array $facts): string
    {
        $payload = [
            'athlete_first_name' => self::firstName($athlete),
            'sport' => $sportName,
            'facts' => $facts,
            'instructions' => 'Return ONLY valid JSON matching the schema described in the system message. No markdown, no code fences.',
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, array<string, mixed>>  $insightFacts
     */
    public static function insightNarrativeUserPrompt(int $organizationId, ?int $userId, array $insightFacts): string
    {
        $payload = [
            'organization_id' => $organizationId,
            'athlete_user_id' => $userId,
            'top_insights' => array_slice($insightFacts, 0, 10),
            'instructions' => 'Return ONLY valid JSON with keys narrative (2-4 sentences), actionable (one short bullet). No markdown.',
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
