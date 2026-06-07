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
use PhpOffice\PhpSpreadsheet\IOFactory;
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
     * Read an Excel file and return all rows as an array of associative arrays.
     * Reads the specific sheet_name configured on the tool.
     */
    public function readExcel(DbTool $tool): array
    {
        $absolutePath = storage_path('app/public/' . $tool->file_path);

        if (!file_exists($absolutePath)) {
            throw new RuntimeException("Le fichier Excel pour l'outil '{$tool->label}' est introuvable.");
        }

        $spreadsheet = IOFactory::load($absolutePath);

        // Load the specific sheet by name
        $sheetName = $tool->sheet_name;
        if ($sheetName && $spreadsheet->sheetNameExists($sheetName)) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
        } else {
            $sheet = $spreadsheet->getActiveSheet();
        }

        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        // First row = headers
        $headers = array_shift($rows);

        // Build array of associative arrays
        $data = [];
        foreach ($rows as $row) {
            // Skip entirely empty rows
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }
            $item = [];
            foreach ($headers as $i => $header) {
                $item[$header] = $row[$i] ?? null;
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     * Get raw data for a Tool (works for both API and Excel types).
     * Used by ToolController@test to preview data.
     */
    public function getRawToolData(DbTool $tool): array
    {
        if ($tool->type === 'excel') {
            return $this->readExcel($tool);
        }

        // For API tools, use the connection
        return $this->apiService->call($tool->apiConnection, $tool->endpoint, $tool->method);
    }

    /**
     * Execute a tool by name for the given ApiConnection.
     * Returns the raw data array from the remote API or Excel file.
     *
     * @throws RuntimeException
     */
    public function execute(string $toolName, ApiConnection $connection): array
    {
        Log::info('[ToolManager] Executing tool', ['tool' => $toolName, 'connection' => $connection->name ?? 'none']);

        // 1. Try dynamic tool from DB (API type — look under the connection)
        $dbTool = DbTool::active()
            ->where('name', $toolName)
            ->where(function($query) use ($connection) {
                $query->where('api_connection_id', $connection->id)
                      ->orWhere('type', 'excel'); // Excel tools have no connection
            })
            ->first();

        if ($dbTool) {
            // Fetch main data (API or Excel)
            $mainData = $this->getRawToolData($dbTool);

            // Check if there are any relations where this tool is involved
            $relations = \App\Models\ToolRelation::where(function($query) use ($connection) {
                    $query->where('api_connection_id', $connection->id)
                          ->orWhereNull('api_connection_id');
                })
                ->where(function($query) use ($dbTool) {
                    $query->where('primary_tool_id', $dbTool->id)
                          ->orWhere('foreign_tool_id', $dbTool->id);
                })
                ->with(['primaryTool', 'foreignTool'])
                ->get();

            if ($relations->isEmpty()) {
                return $mainData;
            }

            // We have relations, build a combined dataset
            $combinedData = [
                "{$dbTool->label} (Outil Principal)" => $mainData,
            ];

            $relationsDescription = [];
            $toolsFetched = [$dbTool->id];

            foreach ($relations as $rel) {
                // Determine the "other" tool
                $otherTool = $rel->primary_tool_id === $dbTool->id ? $rel->foreignTool : $rel->primaryTool;

                if (!in_array($otherTool->id, $toolsFetched)) {
                    try {
                        $otherData = $this->getRawToolData($otherTool);
                        $combinedData["{$otherTool->label} (Outil Lié)"] = $otherData;
                        $toolsFetched[] = $otherTool->id;
                    } catch (\Exception $e) {
                        Log::warning('[ToolManager] Failed to fetch related tool', ['tool' => $otherTool->name, 'error' => $e->getMessage()]);
                        $combinedData["{$otherTool->label} (Outil Lié)"] = "Erreur de récupération : " . $e->getMessage();
                    }
                }

                $relationsDescription[] = "- Le champ `{$rel->primary_field}` de `{$rel->primaryTool->label}` correspond au champ `{$rel->foreign_field}` de `{$rel->foreignTool->label}`.";
            }

            $combinedData["_relations"] = $relationsDescription;

            return $combinedData;
        }

        // 2. Try built-in tool class
        if (isset($this->registry[$toolName])) {
            $toolClass = $this->registry[$toolName];
            $toolInstance = new $toolClass(app(\App\Services\RemoteApiService::class));
            return $this->apiService->call($connection, $toolInstance->getEndpoint(), $toolInstance->getMethod());
        }

        throw new RuntimeException("Outil inconnu : '{$toolName}'.");
    }

    public function getAvailableTools(ApiConnection $connection): array
    {
        $apiTools = DbTool::active()->where('api_connection_id', $connection->id)->pluck('name')->toArray();
        $excelTools = DbTool::active()->where('type', 'excel')->pluck('name')->toArray();
        return array_merge(array_keys($this->registry), $apiTools, $excelTools);
    }
}
