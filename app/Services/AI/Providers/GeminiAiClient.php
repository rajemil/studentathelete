<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiResult;
use App\Services\AI\Contracts\AiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GeminiAiClient implements AiClient
{
    public function __construct(
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
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($this->model),
        );

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url.'?key='.rawurlencode($this->apiKey), [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $userPrompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.35,
                    ],
                ]);

            $latency = (int) (microtime(true) * 1000) - $started;

            if (! $response->successful()) {
                return AiResult::failure('gemini', $this->model, $latency, 'HTTP '.$response->status().': '.$response->body());
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            if (! is_string($text) || $text === '') {
                return AiResult::failure('gemini', $this->model, $latency, 'Empty Gemini response text.');
            }

            $decoded = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                return AiResult::failure('gemini', $this->model, $latency, 'Gemini JSON was not an object.');
            }

            foreach ($requiredTopLevelKeys as $key) {
                if (! array_key_exists($key, $decoded)) {
                    return AiResult::failure('gemini', $this->model, $latency, 'Missing key in JSON: '.$key);
                }
            }

            return AiResult::ok($decoded, 'gemini', $this->model, $latency);
        } catch (Throwable $e) {
            $latency = (int) (microtime(true) * 1000) - $started;
            Log::warning('Gemini AI call failed', ['message' => $e->getMessage()]);

            return AiResult::failure('gemini', $this->model, $latency, $e->getMessage());
        }
    }
}
