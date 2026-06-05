<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RemoteApiService
{
    private int $timeout;

    public function __construct()
    {
        $this->timeout = (int) env('REMOTE_API_TIMEOUT', 30);
    }

    /**
     * Call a remote API endpoint for a given client.
     *
     * @throws RuntimeException
     */
    public function call(Client $client, string $endpoint, string $method = 'GET', array $params = []): array
    {
        $url = rtrim($client->api_url, '/') . '/' . ltrim($endpoint, '/');

        Log::info('[RemoteAPI] Calling endpoint', [
            'client' => $client->name,
            'url'    => $url,
            'method' => $method,
        ]);

        try {
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $client->api_key,
                    'Accept'        => 'application/json',
                    'X-ChatBot'     => 'true',
                ]);

            $response = match(strtoupper($method)) {
                'POST'   => $http->post($url, $params),
                'PUT'    => $http->put($url, $params),
                'DELETE' => $http->delete($url),
                default  => $http->get($url, $params),
            };

            if ($response->unauthorized()) {
                throw new RuntimeException(
                    "Clé API invalide ou expirée pour le client « {$client->name} ». " .
                    "Vérifiez la configuration du client."
                );
            }

            if ($response->notFound()) {
                throw new RuntimeException(
                    "L'endpoint '{$endpoint}' est introuvable sur l'API du client « {$client->name} »."
                );
            }

            if ($response->failed()) {
                throw new RuntimeException(
                    "L'API du client « {$client->name} » a retourné une erreur HTTP {$response->status()}."
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw new RuntimeException(
                    "L'API du client « {$client->name} » a retourné une réponse non-JSON."
                );
            }

            Log::info('[RemoteAPI] Success', ['client' => $client->name, 'endpoint' => $endpoint]);

            return $data;

        } catch (ConnectionException $e) {
            Log::error('[RemoteAPI] Connection failed', [
                'client'   => $client->name,
                'url'      => $url,
                'error'    => $e->getMessage(),
            ]);

            throw new RuntimeException(
                "Impossible de joindre l'API du client « {$client->name} » ({$client->api_url}). " .
                "Vérifiez que l'application distante est en ligne."
            );
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('[RemoteAPI] Request exception', ['error' => $e->getMessage()]);
            throw new RuntimeException("Erreur lors de la requête vers l'API distante : " . $e->getMessage());
        }
    }

    /**
     * Ping the remote API to check connectivity.
     */
    public function ping(Client $client): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => 'Bearer ' . $client->api_key])
                ->get(rtrim($client->api_url, '/'));

            return $response->successful() || $response->status() === 401;
        } catch (\Throwable) {
            return false;
        }
    }
}
