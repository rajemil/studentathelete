<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiResult;
use App\Services\AI\Contracts\AiClient;

final class NullAiClient implements AiClient
{
    public function __construct(
        private readonly string $label = 'none',
    ) {}

    public function generateStructuredJson(
        string $systemPrompt,
        string $userPrompt,
        array $requiredTopLevelKeys = [],
    ): AiResult {
        return AiResult::failure($this->label, null, 0, 'AI provider disabled or not configured.');
    }
}
