<?php

namespace App\AI;

class PromptBuilder
{
    private string $systemRole = "Tu es un assistant intelligent de gestion commerciale, précis et professionnel. Tu réponds toujours en français sauf si l'utilisateur s'adresse à toi dans une autre langue.";

    /**
     * Build a full prompt with context data from a tool response.
     */
    public function buildWithData(string $userMessage, string $toolName, array $data): string
    {
        $formattedData = $this->formatData($data);
        $toolDescription = $this->getToolDescription($toolName);

        return <<<PROMPT
{$this->systemRole}

Contexte : {$toolDescription}

Question de l'utilisateur :
{$userMessage}

Données récupérées depuis le système :
{$formattedData}

Instructions :
- Réponds de manière claire, concise et professionnelle.
- Utilise les données fournies pour formuler ta réponse.
- Ne mentionne pas la source des données (API, système, etc.).
- Si les données sont vides ou nulles, indique poliment qu'aucune information n'est disponible.
- Utilise des chiffres précis issus des données.
- Tu peux utiliser des emojis pour rendre la réponse plus lisible (✅, 📊, 📈, ⚠️, etc.).

Réponse :
PROMPT;
    }

    /**
     * Build a general conversation prompt (no tool data).
     */
    public function buildGeneral(string $userMessage): string
    {
        return <<<PROMPT
{$this->systemRole}

L'utilisateur pose une question générale sans données spécifiques du système.

Question :
{$userMessage}

Instructions :
- Réponds de manière utile et professionnelle.
- Si la question concerne des données métier spécifiques (ventes, stocks, produits...), indique que tu as besoin de précisions ou que la question doit être reformulée.
- Ne génère pas de fausses données.

Réponse :
PROMPT;
    }

    /**
     * Build an error prompt when a tool fails.
     */
    public function buildErrorResponse(string $userMessage, string $errorMessage): string
    {
        return <<<PROMPT
{$this->systemRole}

L'utilisateur a posé cette question : "{$userMessage}"

Une erreur technique s'est produite lors de la récupération des données :
{$errorMessage}

Génère un message d'excuse professionnel et courtois expliquant que les données ne sont pas disponibles temporairement, sans mentionner les détails techniques.

Réponse :
PROMPT;
    }

    /**
     * Convert array data to a human-readable JSON string.
     */
    private function formatData(array $data): string
    {
        if (empty($data)) {
            return 'Aucune donnée disponible.';
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get a human-readable description of what the tool retrieves.
     */
    private function getToolDescription(string $toolName): string
    {
        return match($toolName) {
            'getSalesToday'    => "Données de ventes pour la journée en cours.",
            'getMonthlySales'  => "Données de ventes pour le mois en cours.",
            'getTopProducts'   => "Liste des produits les plus vendus.",
            'getLowStock'      => "Liste des produits en stock faible ou critique.",
            default            => "Données récupérées depuis le système de gestion.",
        };
    }

    /**
     * Change the system role at runtime (e.g. for different domains).
     */
    public function withRole(string $role): static
    {
        $clone = clone $this;
        $clone->systemRole = $role;
        return $clone;
    }
}
