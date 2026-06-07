<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Models\Tool;
use App\Services\DynamicApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\AI\ToolManager; // Note: We will inject ToolManager instead of DynamicApiService later, or just create a new service. Actually, we use ToolManager for execution now!

class ToolController extends Controller
{
    public function index(): View
    {
        $tools   = Tool::with('apiConnection')->latest()->paginate(20);
        $connections = ApiConnection::active()->orderBy('name')->get();

        return view('tools.index', compact('tools', 'connections'));
    }

    public function create(): View
    {
        $connections = ApiConnection::active()->orderBy('name')->get();
        return view('tools.create', compact('connections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'              => 'required|in:api,excel',
            'api_connection_id' => 'required_if:type,api|exists:api_connections,id|nullable',
            'name'              => 'required_if:type,api|string|max:100|nullable',
            'label'             => 'required_if:type,api|string|max:100|nullable',
            'endpoint'          => 'required_if:type,api|string|max:255|nullable',
            'keywords'          => 'required_if:type,api|string|nullable',
            'method'            => 'required_if:type,api|in:GET,POST,PUT,DELETE|nullable',
            'description'       => 'nullable|string|max:1000',
            'active'            => 'boolean',
            'excel_file'        => 'required_if:type,excel|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $active = $request->boolean('active', true);
        $description = $validated['description'] ?? '';

        if ($validated['type'] === 'excel') {
            $file = $request->file('excel_file');
            $path = $file->store('tools', 'public');
            $absolutePath = storage_path('app/public/' . $path);

            $spreadsheet = IOFactory::load($absolutePath);
            $sheetNames = $spreadsheet->getSheetNames();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            foreach ($sheetNames as $sheetName) {
                $finalName = count($sheetNames) === 1 ? $originalName : $sheetName;
                
                Tool::create([
                    'type' => 'excel',
                    'file_path' => $path,
                    'sheet_name' => $sheetName,
                    'name' => 'excel_' . Str::slug($finalName) . '_' . uniqid(),
                    'label' => 'Excel - ' . $finalName,
                    'keywords' => array_map('trim', explode(',', $finalName)),
                    'description' => $description ?: 'Source Excel : ' . $finalName,
                    'active' => $active,
                ]);
            }

            return redirect()->route('tools.index')
                ->with('success', count($sheetNames) . ' Outil(s) Excel créé(s) avec succès.');
        } else {
            // Logic for API
            Tool::create([
                'type' => 'api',
                'api_connection_id' => $validated['api_connection_id'],
                'name' => $validated['name'],
                'label' => $validated['label'],
                'endpoint' => $validated['endpoint'],
                'method' => $validated['method'],
                'keywords' => array_map('trim', explode(',', $validated['keywords'])),
                'description' => $description,
                'active' => $active,
            ]);

            return redirect()->route('tools.index')
                ->with('success', 'Outil API créé avec succès.');
        }
    }

    public function edit(Tool $tool): View
    {
        $connections = ApiConnection::active()->orderBy('name')->get();
        return view('tools.edit', compact('tool', 'connections'));
    }

    public function update(Request $request, Tool $tool): RedirectResponse
    {
        // For simplicity, we just allow updating basic info for Excel, or full update for API.
        if ($tool->type === 'excel') {
            $validated = $request->validate([
                'label'       => 'required|string|max:100',
                'keywords'    => 'required|string',
                'description' => 'nullable|string|max:1000',
                'active'      => 'boolean',
                'excel_file'  => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            $tool->label = $validated['label'];
            $tool->keywords = array_map('trim', explode(',', $validated['keywords']));
            $tool->description = $validated['description'] ?? '';
            $tool->active = $request->has('active');

            if ($request->hasFile('excel_file')) {
                // To replace an existing file, we just overwrite the path (not updating sheet for simplicity, assuming single sheet replacement or keeping the same sheet name).
                // Actually, if we want to support full replace, user should delete and recreate.
                $file = $request->file('excel_file');
                $path = $file->store('tools', 'public');
                $tool->file_path = $path;
            }

            $tool->save();

            return redirect()->route('tools.index')->with('success', 'Outil Excel mis à jour.');
        } else {
            $validated = $request->validate([
                'api_connection_id' => 'required|exists:api_connections,id',
                'name'              => 'required|string|max:100',
                'label'             => 'required|string|max:100',
                'endpoint'          => 'required|string|max:255',
                'description'       => 'nullable|string|max:1000',
                'keywords'          => 'required|string',
                'method'            => 'required|in:GET,POST,PUT,DELETE',
                'active'            => 'boolean',
            ]);

            $validated['keywords'] = array_map('trim', explode(',', $validated['keywords']));
            $validated['active']   = $request->has('active');
            $validated['description'] = $validated['description'] ?? '';

            $tool->update($validated);

            return redirect()->route('tools.index')->with('success', 'Outil API mis à jour.');
        }
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        $tool->delete();
        return redirect()->route('tools.index')
            ->with('success', 'Outil supprimé.');
    }

    // Replace the dependency DynamicApiService with our universal execution layer.
    // We'll create it soon if it doesn't exist. Let's assume \App\AI\ToolManager.
    public function test(Tool $tool): JsonResponse
    {
        try {
            // Using the centralized execute method so "Test" button works for Excel too.
            $toolManager = app(\App\AI\ToolManager::class);
            
            // Note: execute() usually expects tool name and connection, but we can call a direct getToolData method
            $data = $toolManager->getRawToolData($tool);
            
            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
