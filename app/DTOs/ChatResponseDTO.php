<?php

namespace App\DTOs;

readonly class ChatResponseDTO
{
    public function __construct(
        public int     $id,
        public string  $message,
        public ?string $toolUsed,
        public int     $responseTimeMs,
    ) {}

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'message'          => $this->message,
            'tool_used'        => $this->toolUsed,
            'response_time_ms' => $this->responseTimeMs,
        ];
    }
}
