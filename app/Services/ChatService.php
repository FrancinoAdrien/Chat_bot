<?php

namespace App\Services;

use App\AI\IntentDetector;
use App\AI\PromptBuilder;
use App\AI\ToolManager;
use App\Models\AiProviderSetting;
use App\Models\ApiConnection;
use App\Models\Conversation;
use App\Services\GroqService;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(
        private readonly IntentDetector $intentDetector,
        private readonly ToolManager $toolManager,
        private readonly PromptBuilder $promptBuilder,
        private readonly OllamaService $ollamaService,
        private readonly GroqService $groqService
    ) {}

    public function handle(ApiConnection $connection, string $userMessage): Conversation
    {
        $startTime = microtime(true);
        $toolUsed  = null;
        $toolData  = null;

        // 1. Detect Intent
        $toolUsed = $this->intentDetector->detect($userMessage, $connection);

        // 2. Execute Tool if needed
        if ($toolUsed) {
            try {
                $toolData = $this->toolManager->execute($toolUsed, $connection);
                $prompt = $this->promptBuilder->buildWithData($userMessage, $toolUsed, $toolData);
            } catch (\Exception $e) {
                // If API fails (auth error, timeout, etc.), catch it and ask AI to explain it gracefully
                Log::warning('[ChatService] Tool execution error', [
                    'message'    => $e->getMessage(),
                    'tool'       => $toolUsed,
                    'connection' => $connection->name,
                ]);
                $prompt = $this->promptBuilder->buildErrorResponse($userMessage, $e->getMessage());
            }
        } else {
            // General conversation
            $prompt = $this->promptBuilder->buildGeneral($userMessage);
        }

        // 3. Get AI Response — use Groq if available, else Ollama
        try {
            $cloudProvider = AiProviderSetting::activeCloud();

            if ($cloudProvider) {
                Log::info('[ChatService] Using cloud provider', ['provider' => $cloudProvider->provider]);
                $aiResponse = $this->groqService->generate(
                    $prompt,
                    $cloudProvider->api_key,
                    $cloudProvider->model
                );
            } else {
                Log::info('[ChatService] Using Ollama (local)');
                $aiResponse = $this->ollamaService->generate($prompt);
            }
        } catch (\Exception $e) {
            $aiResponse = "Erreur de connexion au moteur IA : " . $e->getMessage();
        }

        // 4. Record Conversation
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        return Conversation::create([
            'api_connection_id' => $connection->id,
            'user_message'      => $userMessage,
            'ai_response'       => $aiResponse,
            'tool_used'         => $toolUsed,
            'tool_data'         => $toolData,
            'response_time_ms'  => $durationMs,
        ]);
    }
}
