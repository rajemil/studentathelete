<?php

namespace App\Services\AI;

final class AiResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $data,
        public readonly string $provider,
        public readonly ?string $model,
        public readonly int $latencyMs,
        public readonly ?string $error = null,
        public readonly bool $usedFallback = false,
    ) {}

    public static function failure(string $provider, ?string $model, int $latencyMs, string $error): self
    {
        return new self(false, null, $provider, $model, $latencyMs, $error, true);
    }

    public static function ok(array $data, string $provider, ?string $model, int $latencyMs): self
    {
        return new self(true, $data, $provider, $model, $latencyMs, null, false);
    }
}
