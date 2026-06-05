<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OllamaService
{
    private string $baseUrl;
    private string $model;
    private int    $timeout;
    private array  $options;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ollama.url', 'http://localhost:11434'), '/');
        $this->model   = config('ollama.model', 'llama3');
        $this->timeout = config('ollama.timeout', 120);
        $this->options = config('ollama.options', []);
    }

    /**
     * Generate a response from Ollama given a prompt.
     *
     * @throws RuntimeException
     */
    public function generate(string $prompt, ?string $model = null): string
    {
        $usedModel = $model ?? $this->model;

        Log::info('[Ollama] Generating response', [
            'model'  => $usedModel,
            'prompt' => substr($prompt, 0, 200) . '...',
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/generate", [
                    'model'   => $usedModel,
                    'prompt'  => $prompt,
                    'stream'  => false,
                    'options' => $this->options,
                ]);

            if ($response->failed()) {
                $status = $response->status();
                Log::error('[Ollama] HTTP error', ['status' => $status, 'body' => $response->body()]);

                throw new RuntimeException(
                    match(true) {
                        $status === 404 => "Le modèle '{$usedModel}' n'est pas disponible sur Ollama. Vérifiez qu'il est bien téléchargé avec `ollama pull {$usedModel}`.",
                        $status === 500 => "Ollama a rencontré une erreur interne. Vérifiez les logs du service Ollama.",
                        default         => "Ollama a retourné une erreur HTTP {$status}.",
                    }
                );
            }

            $data = $response->json();

            if (empty($data['response'])) {
                throw new RuntimeException("Ollama a retourné une réponse vide ou invalide.");
            }

            Log::info('[Ollama] Response received', [
                'eval_duration' => $data['eval_duration'] ?? null,
                'tokens'        => $data['eval_count'] ?? null,
            ]);

            return trim($data['response']);

        } catch (ConnectionException $e) {
            Log::error('[Ollama] Connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException(
                "Impossible de se connecter à Ollama sur {$this->baseUrl}. " .
                "Assurez-vous qu'Ollama est démarré (commande : `ollama serve`)."
            );
        }
    }

    /**
     * Check if Ollama is reachable.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * List available local models.
     */
    public function listModels(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/tags");

            if ($response->successful()) {
                return collect($response->json('models', []))
                    ->pluck('name')
                    ->all();
            }
        } catch (\Throwable) {
            // silent
        }

        return [];
    }

    /**
     * Change model at runtime.
     */
    public function withModel(string $model): static
    {
        $clone = clone $this;
        $clone->model = $model;
        return $clone;
    }
}
