<?php

namespace App\AI;

use App\Models\ApiConnection;
use App\Models\Tool as DbTool;
use App\Services\DynamicApiService;
use App\Tools\GetSalesTodayTool;
use App\Tools\GetMonthlySalesTool;
use App\Tools\GetTopProductsTool;
use App\Tools\GetLowStockTool;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ToolManager
{
    /**
     * Built-in tools registry.
     */
    private array $registry = [
        'getSalesToday'   => GetSalesTodayTool::class,
        'getMonthlySales' => GetMonthlySalesTool::class,
        'getTopProducts'  => GetTopProductsTool::class,
        'getLowStock'     => GetLowStockTool::class,
    ];

    public function __construct(
        private readonly DynamicApiService $apiService
    ) {}

    /**
     * Execute a tool by name for the given ApiConnection.
     * Returns the raw data array from the remote API.
     *
     * @throws RuntimeException
     */
    public function execute(string $toolName, ApiConnection $connection): array
    {
        Log::info('[ToolManager] Executing tool', ['tool' => $toolName, 'connection' => $connection->name]);

        // 1. Try dynamic tool from DB
        $dbTool = DbTool::active()
            ->where('api_connection_id', $connection->id)
            ->where('name', $toolName)
            ->first();

        if ($dbTool) {
            return $this->apiService->call($connection, $dbTool->endpoint, $dbTool->method);
        }

        // 2. Try built-in tool class
        if (isset($this->registry[$toolName])) {
            $toolClass = $this->registry[$toolName];
            // Instantiate without RemoteApiService, we use DynamicApiService now.
            // Wait, native tools might have been injected with RemoteApiService.
            // But since they are native, we can just execute their endpoints.
            $toolInstance = new $toolClass(app(\App\Services\RemoteApiService::class)); 
            
            // Actually, let's just use DynamicApiService to call it natively.
            // We just need the endpoint and method from the tool.
            return $this->apiService->call($connection, $toolInstance->getEndpoint(), $toolInstance->getMethod());
        }

        throw new RuntimeException("Outil inconnu : '{$toolName}'.");
    }

    public function getAvailableTools(ApiConnection $connection): array
    {
        $dbTools = DbTool::active()->where('api_connection_id', $connection->id)->pluck('name')->toArray();
        return array_merge(array_keys($this->registry), $dbTools);
    }
}
