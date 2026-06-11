<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    private int $timeout = 60;

    public function generate(string $prompt, string $apiKey, string $model = 'gemini-3.5-flash'): string
    {
        Log::info('[Gemini] Generating response', ['model' => $model]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 1024,
                        'temperature'     => 0.7,
                    ],
                ]);

            if ($response->status() === 400) {
                $err = $response->json('error.message', 'Requête invalide.');
                throw new RuntimeException("Erreur Gemini : {$err}");
            }
            if ($response->status() === 403) {
                throw new RuntimeException('Clé API Gemini invalide ou accès refusé.');
            }
            if ($response->status() === 429) {
                throw new RuntimeException('Limite de requêtes Gemini atteinte. Réessayez dans un moment.');
            }
            if ($response->failed()) {
                $err = $response->json('error.message', "Erreur HTTP {$response->status()}");
                throw new RuntimeException("Erreur Gemini : {$err}");
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            if (empty($text)) {
                throw new RuntimeException('Gemini a retourné une réponse vide.');
            }

            Log::info('[Gemini] Response received', ['tokens' => $response->json('usageMetadata.candidatesTokenCount')]);
            return trim($text);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Gemini] Connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Impossible de joindre l\'API Gemini. Vérifiez votre connexion internet.');
        }
    }

    public function verify(string $apiKey, string $model = 'gemini-3.5-flash'): bool
    {
        try {
            $response = Http::timeout(15)
                ->post("{$this->baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => 'Hi']]]],
                    'generationConfig' => ['maxOutputTokens' => 5],
                ]);

            if ($response->status() === 403) {
                throw new RuntimeException('Clé API invalide. Vérifiez votre clé sur aistudio.google.com.');
            }
            if ($response->status() === 404) {
                throw new RuntimeException("Le modèle « {$model} » n'existe pas ou n'est pas disponible.");
            }

            return $response->successful();

        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new RuntimeException('Impossible de joindre l\'API Gemini. Vérifiez votre connexion internet.');
        }
    }
}
