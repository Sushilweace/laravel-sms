<?php

namespace App\Services\Ai\Contracts;

interface AiGatewayInterface
{
    public function generate(string $prompt, string $feature, array $options = []): string;
}
