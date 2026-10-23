<?php

namespace App\Services\Ai;

use App\Models\AiRequest;
use App\Models\AiSuggestion;
use App\Models\User;
use App\Services\Ai\Contracts\AiGatewayInterface;
use App\Services\Ai\Drivers\FakeAiDriver;

class AiGateway
{
    protected AiGatewayInterface $driver;

    public function __construct(?AiGatewayInterface $driver = null)
    {
        $this->driver = $driver ?? new FakeAiDriver;
    }

    public function ask(string $feature, string $prompt, string $targetType, int $targetId, array $options = [], ?User $user = null): AiSuggestion
    {
        $response = $this->driver->generate($prompt, $feature, $options);

        $aiRequest = AiRequest::create([
            'feature' => $feature,
            'prompt_version' => 'v1',
            'model' => 'system-ai-v1',
            'tokens_used' => strlen($prompt) + strlen($response),
            'cost' => '0.0000',
            'requested_by' => $user?->id,
            'response_payload' => $response,
            'status' => 'completed',
        ]);

        return AiSuggestion::create([
            'ai_request_id' => $aiRequest->id,
            'feature' => $feature,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'suggested_content' => $response,
            'status' => 'pending',
        ]);
    }
}
