<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Models\Tool;
use App\Models\ToolRelation;
use App\Services\DynamicApiService;
use App\AI\ToolManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ToolRelationController extends Controller
{
    public function __construct(
        private readonly DynamicApiService $apiService,
        private readonly ToolManager $toolManager,
    ) {}

    /**
     * Show the ERD visual builder page.
     */
    public function index(Request $request): View
    {
        $tools = Tool::active()->get();
        $relations = ToolRelation::with(['primaryTool', 'foreignTool'])->get();

        return view('tools.relations', compact('tools', 'relations'));
    }

    /**
     * Fetch the schema (column names) for a given tool.
     * Handles both API tools and Excel tools.
     */
    public function schema(Tool $tool): JsonResponse
    {
        try {
            if ($tool->type === 'excel') {
                // Read headers from Excel file
                $absolutePath = storage_path('app/public/' . $tool->file_path);

                if (!file_exists($absolutePath)) {
                    throw new \RuntimeException("Le fichier Excel est introuvable.");
                }

                $spreadsheet = IOFactory::load($absolutePath);
                $sheetName = $tool->sheet_name;
                $sheet = ($sheetName && $spreadsheet->sheetNameExists($sheetName))
                    ? $spreadsheet->getSheetByName($sheetName)
                    : $spreadsheet->getActiveSheet();

                $firstRow = [];
                foreach ($sheet->getRowIterator(1, 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $val = trim((string) $cell->getFormattedValue());
                        if ($val !== '') {
                            $firstRow[] = $val;
                        }
                    }
                }

                return response()->json([
                    'success'   => true,
                    'fields'    => $firstRow,
                    'tool_id'   => $tool->id,
                    'tool_name' => $tool->label,
                    'tool_type' => 'excel',
                ]);
            }

            // --- API tool ---
            $data = $this->apiService->call(
                $tool->apiConnection,
                $tool->endpoint,
                $tool->method
            );

            // Extract field names from the first record
            $fields = [];
            $sampleData = $data;

            if (isset($data['data']) && is_array($data['data'])) {
                $sampleData = $data['data'];
            }

            if (is_array($sampleData) && !empty($sampleData)) {
                $firstItem = reset($sampleData);
                $fields = is_array($firstItem) ? array_keys($firstItem) : array_keys($sampleData);
            }

            return response()->json([
                'success'   => true,
                'fields'    => $fields,
                'tool_id'   => $tool->id,
                'tool_name' => $tool->label,
                'tool_type' => 'api',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'fields'  => [],
            ], 400);
        }
    }

    /**
     * Store a new relation between two tools.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_connection_id' => 'nullable|exists:api_connections,id',
            'primary_tool_id'   => 'required|exists:tools,id',
            'primary_field'     => 'required|string|max:100',
            'foreign_tool_id'   => 'required|exists:tools,id|different:primary_tool_id',
            'foreign_field'     => 'required|string|max:100',
        ]);

        // Check duplicate
        $exists = ToolRelation::where('primary_tool_id', $validated['primary_tool_id'])
            ->where('primary_field', $validated['primary_field'])
            ->where('foreign_tool_id', $validated['foreign_tool_id'])
            ->where('foreign_field', $validated['foreign_field'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Cette relation existe déjà.',
            ], 422);
        }

        $relation = ToolRelation::create($validated);
        $relation->load(['primaryTool', 'foreignTool']);

        return response()->json([
            'success'  => true,
            'relation' => $relation,
        ]);
    }

    /**
     * Delete a relation.
     */
    public function destroy(ToolRelation $relation): JsonResponse
    {
        $relation->delete();

        return response()->json(['success' => true]);
    }
}
