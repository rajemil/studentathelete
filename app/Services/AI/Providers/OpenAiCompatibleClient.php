<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiResult;
use App\Services\AI\Contracts\AiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI Chat Completions and xAI Grok (OpenAI-compatible) JSON mode.
 */
final class OpenAiCompatibleClient implements AiClient
{
    public function __construct(
        private readonly string $providerLabel,
        private readonly string $endpoint,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeoutSeconds,
    ) {}

    public function generateStructuredJson(
        string $systemPrompt,
        string $userPrompt,
        array $requiredTopLevelKeys = [],
    ): AiResult {
        $started = (int) (microtime(true) * 1000);

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withToken($this->apiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.35,
                ]);

            $latency = (int) (microtime(true) * 1000) - $started;

            if (! $response->successful()) {
                return AiResult::failure($this->providerLabel, $this->model, $latency, 'HTTP '.$response->status().': '.$response->body());
            }

            $content = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($content) || $content === '') {
                return AiResult::failure($this->providerLabel, $this->model, $latency, 'Empty message content.');
            }

            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                return AiResult::failure($this->providerLabel, $this->model, $latency, 'JSON was not an object.');
            }

            foreach ($requiredTopLevelKeys as $key) {
                if (! array_key_exists($key, $decoded)) {
                    return AiResult::failure($this->providerLabel, $this->model, $latency, 'Missing key in JSON: '.$key);
                }
            }

            return AiResult::ok($decoded, $this->providerLabel, $this->model, $latency);
        } catch (Throwable $e) {
            $latency = (int) (microtime(true) * 1000) - $started;
            Log::warning('OpenAI-compatible AI call failed', ['provider' => $this->providerLabel, 'message' => $e->getMessage()]);

            return AiResult::failure($this->providerLabel, $this->model, $latency, $e->getMessage());
        }
    }
}
