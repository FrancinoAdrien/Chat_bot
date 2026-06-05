<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Models\Tool;
use App\Services\DynamicApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'api_connection_id' => 'required|exists:api_connections,id',
            'name'              => 'required|string|max:100',
            'label'             => 'required|string|max:100',
            'endpoint'          => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'keywords'          => 'required|string', // on stocke en json
            'method'            => 'required|in:GET,POST,PUT,DELETE',
            'active'            => 'boolean',
        ]);

        $validated['keywords'] = array_map('trim', explode(',', $validated['keywords']));
        $validated['active']   = $request->boolean('active', true);
        $validated['description'] = $validated['description'] ?? '';

        Tool::create($validated);

        return redirect()->route('tools.index')
            ->with('success', 'Outil créé avec succès.');
    }

    public function edit(Tool $tool): View
    {
        $connections = ApiConnection::active()->orderBy('name')->get();
        return view('tools.edit', compact('tool', 'connections'));
    }

    public function update(Request $request, Tool $tool): RedirectResponse
    {
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

        return redirect()->route('tools.index')
            ->with('success', 'Outil mis à jour avec succès.');
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        $tool->delete();
        return redirect()->route('tools.index')
            ->with('success', 'Outil supprimé.');
    }

    public function test(Tool $tool, DynamicApiService $apiService): JsonResponse
    {
        try {
            $data = $apiService->call($tool->apiConnection, $tool->endpoint, $tool->method);
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
