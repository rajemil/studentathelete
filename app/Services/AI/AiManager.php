<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiClient;
use App\Services\AI\Providers\GeminiAiClient;
use App\Services\AI\Providers\NullAiClient;
use App\Services\AI\Providers\OpenAiCompatibleClient;

final class AiManager
{
    public function __construct(
        private readonly ?AiClient $resolved = null,
    ) {}

    public static function make(): AiClient
    {
        $provider = strtolower((string) config('services.ai.provider', 'none'));

        return match ($provider) {
            'gemini' => self::makeGemini(),
            'openai' => self::makeOpenAi(),
            'grok' => self::makeGrok(),
            default => new NullAiClient($provider ?: 'none'),
        };
    }

    public function client(): AiClient
    {
        return $this->resolved ?? self::make();
    }

    public function isEnabled(): bool
    {
        $client = $this->client();

        return ! $client instanceof NullAiClient;
    }

    public static function isLlmAvailable(): bool
    {
        return ! (self::make() instanceof NullAiClient);
    }

    private static function makeGemini(): AiClient
    {
        $key = (string) config('services.ai.gemini.key', '');
        if ($key === '') {
            return new NullAiClient('gemini');
        }

        return new GeminiAiClient(
            $key,
            (string) config('services.ai.gemini.model', 'gemini-1.5-flash'),
            (int) config('services.ai.timeout', 25),
        );
    }

    private static function makeOpenAi(): AiClient
    {
        $key = (string) config('services.ai.openai.key', '');
        if ($key === '') {
            return new NullAiClient('openai');
        }

        return new OpenAiCompatibleClient(
            'openai',
            'https://api.openai.com/v1/chat/completions',
            $key,
            (string) config('services.ai.openai.model', 'gpt-4o-mini'),
            (int) config('services.ai.timeout', 25),
        );
    }

    private static function makeGrok(): AiClient
    {
        $key = (string) config('services.ai.grok.key', '');
        if ($key === '') {
            return new NullAiClient('grok');
        }

        return new OpenAiCompatibleClient(
            'grok',
            'https://api.x.ai/v1/chat/completions',
            $key,
            (string) config('services.ai.grok.model', 'grok-2-latest'),
            (int) config('services.ai.timeout', 25),
        );
    }
}
