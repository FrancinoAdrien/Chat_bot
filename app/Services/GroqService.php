<?php

namespace App\Services;

use App\Models\AiProviderSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GroqService
{
    private string $baseUrl = 'https://api.groq.com/openai/v1';
    private int $timeout = 60;

    /**
     * Generate a chat completion using Groq API.
     *
     * @throws RuntimeException
     */
    public function generate(string $prompt, string $apiKey, string $model = 'llama-3.3-70b-versatile'): string
    {
        Log::info('[Groq] Generating response', ['model' => $model]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $model,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens'  => 1024,
                    'temperature' => 0.7,
                ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Clé API Groq invalide ou expirée.');
            }

            if ($response->status() === 429) {
                throw new RuntimeException('Limite de requêtes Groq atteinte. Réessayez dans un moment.');
            }

            if ($response->failed()) {
                $err = $response->json('error.message', "Erreur HTTP {$response->status()}");
                throw new RuntimeException("Erreur Groq : {$err}");
            }

            $text = $response->json('choices.0.message.content');

            if (empty($text)) {
                throw new RuntimeException('Groq a retourné une réponse vide.');
            }

            Log::info('[Groq] Response received', [
                'tokens' => $response->json('usage.completion_tokens'),
            ]);

            return trim($text);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Groq] Connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Impossible de joindre l\'API Groq. Vérifiez votre connexion internet.');
        }
    }

    /**
     * Verify an API key by sending a minimal test request.
     *
     * @throws RuntimeException if the key is invalid
     */
    public function verify(string $apiKey, string $model = 'llama-3.3-70b-versatile'): bool
    {
        try {
            $response = Http::timeout(15)
                ->withToken($apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'      => $model,
                    'messages'   => [['role' => 'user', 'content' => 'Hi']],
                    'max_tokens' => 5,
                ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Clé API invalide. Vérifiez votre clé sur console.groq.com.');
            }

            if ($response->status() === 404) {
                throw new RuntimeException("Le modèle « {$model} » n'existe pas sur Groq.");
            }

            return $response->successful();

        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new RuntimeException('Impossible de joindre api.groq.com. Vérifiez votre connexion internet.');
        }
    }
}
