<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiService
{
    private string $baseUrl = 'https://api.openai.com/v1';
    private int $timeout = 60;

    public function generate(string $prompt, string $apiKey, string $model = 'gpt-4o-mini'): string
    {
        Log::info('[OpenAI] Generating response', ['model' => $model]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $model,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens'  => 1024,
                    'temperature' => 0.7,
                ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Clé API OpenAI invalide ou expirée.');
            }
            if ($response->status() === 429) {
                throw new RuntimeException('Limite de requêtes OpenAI atteinte. Réessayez dans un moment.');
            }
            if ($response->failed()) {
                $err = $response->json('error.message', "Erreur HTTP {$response->status()}");
                throw new RuntimeException("Erreur OpenAI : {$err}");
            }

            $text = $response->json('choices.0.message.content');
            if (empty($text)) {
                throw new RuntimeException('OpenAI a retourné une réponse vide.');
            }

            Log::info('[OpenAI] Response received', ['tokens' => $response->json('usage.completion_tokens')]);
            return trim($text);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[OpenAI] Connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Impossible de joindre l\'API OpenAI. Vérifiez votre connexion internet.');
        }
    }

    public function verify(string $apiKey, string $model = 'gpt-4o-mini'): bool
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
                throw new RuntimeException('Clé API invalide. Vérifiez votre clé sur platform.openai.com.');
            }
            if ($response->status() === 404) {
                throw new RuntimeException("Le modèle « {$model} » n'existe pas sur OpenAI.");
            }

            return $response->successful();

        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new RuntimeException('Impossible de joindre api.openai.com. Vérifiez votre connexion internet.');
        }
    }
}
