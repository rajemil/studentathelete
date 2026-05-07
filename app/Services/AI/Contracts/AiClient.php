<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\AiResult;

interface AiClient
{
    /**
     * Ask the model to return JSON only. The implementation should parse and return decoded array or failure.
     *
     * @param  list<string>  $requiredTopLevelKeys  Keys that must exist in the decoded object (validation hint).
     */
    public function generateStructuredJson(
        string $systemPrompt,
        string $userPrompt,
        array $requiredTopLevelKeys = [],
    ): AiResult;
}
