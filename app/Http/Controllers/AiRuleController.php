<?php

namespace App\Http\Controllers;

use App\Models\AiRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiRuleController extends Controller
{
    /**
     * Show the rules management page.
     */
    public function index(): View
    {
        $rules = AiRule::orderBy('created_at', 'desc')->get();
        // Get unique postes and users for the dropdowns
        $postes = User::whereNotNull('poste')->distinct()->pluck('poste');
        $users = User::orderBy('name')->get();

        return view('ai-rules.index', compact('rules', 'postes', 'users'));
    }

    /**
     * Store a new AI rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'instruction'  => 'required|string',
            'target_type'  => 'required|in:all,poste,user',
            'target_value' => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Logic cleanup: if target_type is all, value is null
        if ($validated['target_type'] === 'all') {
            $validated['target_value'] = null;
        }

        AiRule::create($validated);

        return redirect()->route('ai-rules.index')->with('success', 'La règle IA a été créée avec succès.');
    }

    /**
     * Update an AI rule.
     */
    public function update(Request $request, AiRule $aiRule)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'instruction'  => 'required|string',
            'target_type'  => 'required|in:all,poste,user',
            'target_value' => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['target_type'] === 'all') {
            $validated['target_value'] = null;
        }

        $aiRule->update($validated);

        return redirect()->route('ai-rules.index')->with('success', 'La règle IA a été mise à jour.');
    }

    /**
     * Delete an AI rule.
     */
    public function destroy(AiRule $aiRule)
    {
        $aiRule->delete();

        return redirect()->route('ai-rules.index')->with('success', 'La règle a été supprimée.');
    }

    /**
     * Toggle active status via AJAX.
     */
    public function toggle(AiRule $aiRule)
    {
        $aiRule->update(['is_active' => !$aiRule->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $aiRule->is_active
        ]);
    }
}
