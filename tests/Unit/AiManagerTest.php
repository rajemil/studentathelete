<?php

namespace Tests\Unit;

use App\Services\AI\AiManager;
use App\Services\AI\Providers\NullAiClient;
use Tests\TestCase;

class AiManagerTest extends TestCase
{
    public function test_llm_disabled_when_provider_is_none(): void
    {
        config(['services.ai.provider' => 'none']);

        $this->assertFalse(AiManager::isLlmAvailable());
        $this->assertInstanceOf(NullAiClient::class, AiManager::make());
    }

    public function test_gemini_without_key_resolves_to_null_client(): void
    {
        config(['services.ai.provider' => 'gemini', 'services.ai.gemini.key' => '']);

        $this->assertFalse(AiManager::isLlmAvailable());
        $this->assertInstanceOf(NullAiClient::class, AiManager::make());
    }
}
