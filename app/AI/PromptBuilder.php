<?php

namespace App\AI;

class PromptBuilder
{
    private string $systemRole = "Tu es un assistant intelligent de gestion commerciale, précis et professionnel. Tu réponds toujours en français sauf si l'utilisateur s'adresse à toi dans une autre langue.";

    /**
     * Format conversation history for the prompt.
     */
    private function formatHistory(\Illuminate\Database\Eloquent\Collection|array $history): string
    {
        if (empty($history) || (is_object($history) && $history->isEmpty())) {
            return "";
        }

        $text = "\n\nHISTORIQUE RÉCENT DE LA SESSION COURANTE :\n";
        // Take the last 6 messages to avoid token limit overflow
        $recent = is_object($history) ? $history->take(-6) : array_slice($history, -6);
        
        foreach ($recent as $msg) {
            $text .= "- Utilisateur : " . $msg->user_message . "\n";
            $text .= "- Toi : " . $msg->ai_response . "\n\n";
        }
        
        $text .= "NOTE : L'historique ci-dessus est pour ton contexte immédiat.\n";
        return $text;
    }

    /**
     * Format global conversation history (RAG).
     */
    private function formatGlobalHistory(\Illuminate\Database\Eloquent\Collection|array $globalHistory): string
    {
        if (empty($globalHistory) || (is_object($globalHistory) && $globalHistory->isEmpty())) {
            return "";
        }

        $text = "\n\nCONTEXTE HISTORIQUE GLOBAL (Pour référence) :\n";
        $text .= "L'utilisateur a déjà abordé des sujets similaires par le passé. Voici quelques échanges utiles en guise de repère :\n";
        foreach ($globalHistory as $msg) {
            $text .= "[Ancienne Question] : " . $msg->user_message . "\n";
            $text .= "[Ancienne Réponse] : " . $msg->ai_response . "\n\n";
        }
        
        $text .= "NOTE : N'utilise ces informations que si elles sont pertinentes pour répondre à la NOUVELLE question.\n";
        return $text;
    }

    /**
     * Format Admin AI Rules.
     */
    private function formatAiRules(\Illuminate\Database\Eloquent\Collection|array $aiRules): string
    {
        if (empty($aiRules) || (is_object($aiRules) && $aiRules->isEmpty())) {
            return "";
        }

        $text = "\n\nRÈGLES D'ENTREPRISE STRICTES (DIRECTIVES ADMINISTRATEUR) :\n";
        $text .= "Tu dois IMPÉRATIVEMENT respecter les règles suivantes sous peine de sanctions :\n";
        foreach ($aiRules as $rule) {
            $text .= "- " . $rule->instruction . "\n";
        }
        return $text;
    }

    /**
     * Build a full prompt with context data from a tool response.
     */
    public function buildWithData(string $userMessage, string $toolName, array $data, $history = [], $globalHistory = [], $aiRules = []): string
    {
        $relationsText = "";
        
        if (isset($data['_relations'])) {
            $relations = $data['_relations'];
            unset($data['_relations']);
            
            $relationsText = "\n\nATTENTION - Des relations entre tables (bases de données) existent :\n" . implode("\n", $relations) . "\nUtilise ces relations pour croiser les données entre les différentes tables fournies afin de répondre avec précision.";
        }

        $formattedData = $this->formatData($data);
        $toolDescription = $this->getToolDescription($toolName);
        $currentDate = now()->format('l, d F Y H:i:s');
        $historyText = $this->formatHistory($history);
        $globalHistoryText = $this->formatGlobalHistory($globalHistory);
        $aiRulesText = $this->formatAiRules($aiRules);

        return <<<PROMPT
{$this->systemRole}
{$aiRulesText}

CONCEPTE DE TEMPS : Nous sommes actuellement le {$currentDate}. Utilise cette date de référence absolue lorsque l'utilisateur parle de "aujourd'hui", "hier", "ce mois-ci", etc.
{$globalHistoryText}{$historyText}
Contexte : {$toolDescription}{$relationsText}

Dernière question de l'utilisateur :
{$userMessage}

Données récupérées depuis le système :
{$formattedData}

Instructions :
- Réponds de manière claire, concise et professionnelle.
- S'il y a plusieurs tables de données, effectue une jointure mentale en te basant sur les "relations entre tables" spécifiées dans le contexte. Fais attention, les clés peuvent être des textes (ex: "13") ou des nombres (ex: 13), traite-les de manière identique.
- Utilise les données fournies pour formuler ta réponse en tenant compte du contexte de la conversation.
- Ne mentionne pas la source des données ni le fait que tu fais une jointure.
- Si les données sont vides ou nulles, indique poliment qu'aucune information n'est disponible.
- Utilise des chiffres précis issus des données.
- Tu peux utiliser des emojis pour rendre la réponse plus lisible.
- Surtout, N'OUBLIE PAS DE RESPECTER LES RÈGLES D'ENTREPRISE STRICTES si elles sont spécifiées.

Réponse :
PROMPT;
    }

    /**
     * Build a general conversation prompt (no tool data).
     */
    public function buildGeneral(string $userMessage, $history = [], $globalHistory = [], $aiRules = []): string
    {
        $currentDate = now()->format('l, d F Y H:i:s');
        $historyText = $this->formatHistory($history);
        $globalHistoryText = $this->formatGlobalHistory($globalHistory);
        $aiRulesText = $this->formatAiRules($aiRules);

        return <<<PROMPT
{$this->systemRole}
{$aiRulesText}

CONCEPTE DE TEMPS : Nous sommes actuellement le {$currentDate}. Utilise cette date de référence absolue lorsque l'utilisateur parle de "aujourd'hui", "hier", "ce mois-ci", etc.
{$globalHistoryText}{$historyText}
L'utilisateur pose une question générale sans données spécifiques du système. 
Utilise l'historique de la conversation pour comprendre le contexte de sa question si nécessaire.
N'oublie pas de respecter les RÈGLES D'ENTREPRISE STRICTES.

Dernière question de l'utilisateur :
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
