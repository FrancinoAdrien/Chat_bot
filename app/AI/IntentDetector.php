<?php

namespace App\AI;

use App\Models\ApiConnection;
use App\Models\Tool as DbTool;
use Illuminate\Support\Facades\Log;

class IntentDetector
{
    /**
     * Map native tools to their keywords.
     */
    private array $nativeTools = [
        'getSalesToday'   => ['ventes aujourd\'hui', 'chiffre d\'affaires', 'sales today', 'today'],
        'getMonthlySales' => ['ventes du mois', 'mois en cours', 'monthly sales'],
        'getTopProducts'  => ['top produits', 'meilleurs produits', 'best sellers'],
        'getLowStock'     => ['rupture', 'faible stock', 'low stock'],
    ];

    /**
     * Detect which tool should be used based on the user's message.
     * Returns the tool name as a string, or null if no intent is detected.
     */
    public function detect(string $message, ApiConnection $connection, ?\App\Models\ChatSession $session = null): ?string
    {
        $message = mb_strtolower(trim($message));

        // 1. Check dynamic tools for this connection (API type)
        $dbTools = DbTool::active()->where('api_connection_id', $connection->id)->get();

        foreach ($dbTools as $tool) {
            foreach ($tool->keywords as $keyword) {
                if (str_contains($message, mb_strtolower($keyword))) {
                    Log::info('[IntentDetector] DB tool matched', ['tool' => $tool->name, 'message' => $message]);
                    return $tool->name;
                }
            }
        }

        // 1b. Also check Excel tools (they are not tied to a specific connection)
        $excelTools = DbTool::active()->where('type', 'excel')->get();

        foreach ($excelTools as $tool) {
            foreach ($tool->keywords as $keyword) {
                if (str_contains($message, mb_strtolower($keyword))) {
                    Log::info('[IntentDetector] Excel tool matched', ['tool' => $tool->name, 'message' => $message]);
                    return $tool->name;
                }
            }
        }

        // 2. Check native tools
        foreach ($this->nativeTools as $toolName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, mb_strtolower($keyword))) {
                    Log::info('[IntentDetector] Native tool matched', ['tool' => $toolName, 'message' => $message]);
                    return $toolName;
                }
            }
        }

        // 3. Fallback to the last used tool in this session if no explicit keyword matched
        if ($session && $session->exists) {
            $lastConversation = $session->conversations()
                ->whereNotNull('tool_used')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastConversation) {
                Log::info('[IntentDetector] Fallback to last tool', ['tool' => $lastConversation->tool_used]);
                return $lastConversation->tool_used;
            }
        }

        return null;
    }
}
