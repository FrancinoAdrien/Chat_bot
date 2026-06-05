<?php

namespace App\Services;

use App\Models\ApiConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DynamicApiService
{
    private int $timeout;

    public function __construct()
    {
        $this->timeout = (int) env('REMOTE_API_TIMEOUT', 30);
    }

    /**
     * Make a request to a given ApiConnection at a given endpoint.
     *
     * @throws RuntimeException with a user-friendly message
     */
    public function call(ApiConnection $connection, string $endpoint, string $method = 'GET', array $params = []): array
    {
        $url = $connection->buildUrl($endpoint);

        Log::info('[DynamicApiService] Calling', [
            'connection' => $connection->name,
            'url'        => $url,
            'method'     => $method,
        ]);

        try {
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept'     => 'application/json',
                    'X-ChatBot'  => 'true',
                ]);

            // Attach Bearer token if authenticated
            if ($connection->is_authenticated && $connection->auth_token) {
                $http = $http->withToken($connection->auth_token);
            }

            $response = match(strtoupper($method)) {
                'POST'   => $http->post($url, $params),
                'PUT'    => $http->put($url, $params),
                'DELETE' => $http->delete($url),
                default  => $http->get($url, $params),
            };

            // Handle 401/403 — authentication issues
            if ($response->status() === 401 || $response->status() === 403) {
                Log::warning('[DynamicApiService] Auth error', [
                    'connection' => $connection->name,
                    'status'     => $response->status(),
                ]);

                // Mark connection as unauthenticated
                $connection->update([
                    'is_authenticated' => false,
                    'auth_token'       => null,
                ]);

                throw new RuntimeException(
                    "Impossible de récupérer les données sur **{$connection->name}**. " .
                    "Soit il y a un problème de serveur, soit vous n'êtes pas authentifié sur cette API. " .
                    "Veuillez vérifier l'onglet **Connexions**."
                );
            }

            if ($response->notFound()) {
                throw new RuntimeException(
                    "L'endpoint `{$endpoint}` est introuvable sur **{$connection->name}**."
                );
            }

            if ($response->failed()) {
                throw new RuntimeException(
                    "Impossible de récupérer les données sur **{$connection->name}**. " .
                    "L'API a retourné une erreur HTTP {$response->status()}. " .
                    "Veuillez vérifier l'onglet **Connexions**."
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw new RuntimeException(
                    "L'API **{$connection->name}** a retourné une réponse invalide (non-JSON)."
                );
            }

            return $data;

        } catch (ConnectionException $e) {
            Log::error('[DynamicApiService] Connection failed', [
                'connection' => $connection->name,
                'url'        => $url,
                'error'      => $e->getMessage(),
            ]);

            throw new RuntimeException(
                "Impossible de récupérer les données sur **{$connection->name}**. " .
                "Le serveur distant est injoignable ({$connection->base_url}). " .
                "Veuillez vérifier l'onglet **Connexions**."
            );
        }
    }

    /**
     * Authenticate against a login endpoint and store the returned token.
     *
     * @throws RuntimeException
     */
    public function authenticate(ApiConnection $connection, string $email, string $password): string
    {
        if (! $connection->hasLoginUrl()) {
            throw new RuntimeException("Cette connexion n'a pas d'URL de login configurée.");
        }

        $url = $connection->buildUrl($connection->login_url);

        Log::info('[DynamicApiService] Authenticating', [
            'connection' => $connection->name,
            'url'        => $url,
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($url, [
                    'email'    => $email,
                    'password' => $password,
                ]);

            if ($response->status() === 422) {
                $errors = $response->json('errors', []);
                $firstError = collect($errors)->flatten()->first() ?? 'Identifiants invalides.';
                throw new RuntimeException($firstError);
            }

            if ($response->failed()) {
                throw new RuntimeException(
                    "L'authentification a échoué (HTTP {$response->status()}). Vérifiez l'URL de login et vos identifiants."
                );
            }

            // Support multiple token response shapes
            $token = $response->json('token')
                ?? $response->json('access_token')
                ?? $response->json('data.token')
                ?? null;

            if (! $token) {
                throw new RuntimeException(
                    "L'API a répondu mais aucun token n'a été trouvé dans la réponse. " .
                    "Vérifiez que l'endpoint retourne bien un champ `token` ou `access_token`."
                );
            }

            // Save the token
            $connection->update([
                'auth_token'        => $token,
                'is_authenticated'  => true,
                'authenticated_at'  => now(),
            ]);

            Log::info('[DynamicApiService] Authenticated successfully', ['connection' => $connection->name]);

            return $token;

        } catch (ConnectionException $e) {
            throw new RuntimeException(
                "Impossible de joindre {$connection->base_url} pour l'authentification. " .
                "Vérifiez que le serveur distant est en ligne."
            );
        }
    }

    /**
     * Store a raw token directly (skip login flow).
     */
    public function authenticateWithToken(ApiConnection $connection, string $token): void
    {
        $connection->update([
            'auth_token'       => $token,
            'is_authenticated' => true,
            'authenticated_at' => now(),
        ]);

        Log::info('[DynamicApiService] Token stored directly', ['connection' => $connection->name]);
    }

    /**
     * Disconnect / clear the token.
     */
    public function disconnect(ApiConnection $connection): void
    {
        $connection->update([
            'auth_token'       => null,
            'is_authenticated' => false,
            'authenticated_at' => null,
        ]);

        Log::info('[DynamicApiService] Disconnected', ['connection' => $connection->name]);
    }

    /**
     * Test connectivity without needing auth.
     */
    public function ping(ApiConnection $connection): bool
    {
        try {
            $response = Http::timeout(5)->get($connection->base_url);
            return $response->successful() || in_array($response->status(), [401, 403]);
        } catch (\Throwable) {
            return false;
        }
    }
}
