<?php

namespace App\Services;

use App\AI\IntentDetector;
use App\AI\PromptBuilder;
use App\AI\ToolManager;
use App\Models\AiProviderSetting;
use App\Models\ApiConnection;
use App\Models\ChatSession;
use App\Models\Conversation;
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

    /**
     * Handle a chat message. Creates or reuses a ChatSession.
     * Returns an array with the Conversation and the ChatSession.
     */
    public function handle(ApiConnection $connection, string $userMessage, ?int $sessionId = null): array
    {
        $startTime = microtime(true);
        $toolUsed  = null;
        $toolData  = null;
        $userId    = auth()->id();

        // 1. Resolve or create ChatSession
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $userId)
                ->first();
        }

        if (empty($session)) {
            $session = ChatSession::create([
                'user_id'           => $userId,
                'api_connection_id' => $connection->id,
                'title'             => ChatSession::titleFromMessage($userMessage),
                'last_message_at'   => now(),
            ]);
        } else {
            $session->update(['last_message_at' => now()]);
        }

        // Fetch local session history
        $history = [];
        if (!empty($session) && $session->exists) {
            $history = $session->conversations()->orderBy('created_at', 'asc')->get();
        }

        // 2. Fetch Long-term Memory (Global History / RAG)
        $globalHistory = collect();
        if (strlen($userMessage) > 3) {
            $globalHistory = Conversation::where('user_id', $userId)
                ->where('chat_session_id', '!=', $session->id ?? 0) // Exclude current session
                ->whereRaw("MATCH(user_message, ai_response) AGAINST(? IN NATURAL LANGUAGE MODE)", [$userMessage])
                ->limit(3)
                ->get();
        }

        // 3. Fetch Admin Rules (AI Rules) for the user
        $aiRules = \App\Models\AiRule::getRulesForUser(auth()->user());

        // 4. Detect Intent
        $toolUsed = $this->intentDetector->detect($userMessage, $connection, $session);

        // 5. Execute Tool if needed
        if ($toolUsed) {
            try {
                $toolData = $this->toolManager->execute($toolUsed, $connection);
                $prompt = $this->promptBuilder->buildWithData($userMessage, $toolUsed, $toolData, $history, $globalHistory, $aiRules);
            } catch (\Exception $e) {
                Log::warning('[ChatService] Tool execution error', [
                    'message'    => $e->getMessage(),
                    'tool'       => $toolUsed,
                    'connection' => $connection->name,
                ]);
                $prompt = $this->promptBuilder->buildErrorResponse($userMessage, $e->getMessage());
            }
        } else {
            $prompt = $this->promptBuilder->buildGeneral($userMessage, $history, $globalHistory, $aiRules);
        }

        // 4. Get AI Response — use Groq if available, else Ollama
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

        // 5. Record Conversation
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $conversation = Conversation::create([
            'chat_session_id'   => $session->id,
            'user_id'           => $userId,
            'api_connection_id' => $connection->id,
            'user_message'      => $userMessage,
            'ai_response'       => $aiResponse,
            'tool_used'         => $toolUsed,
            'tool_data'         => $toolData,
            'response_time_ms'  => $durationMs,
        ]);

        return [
            'conversation' => $conversation,
            'session'      => $session,
        ];
    }
}
