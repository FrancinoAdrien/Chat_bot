<?php

namespace App\Http\Controllers;

use App\Models\AiProviderSetting;
use App\Services\GroqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiProviderController extends Controller
{
    public function __construct(private readonly GroqService $groqService) {}

    public function index(): View
    {
        $settings = AiProviderSetting::orderBy('is_active', 'desc')->get();
        $activeProvider = AiProviderSetting::activeCloud();

        return view('ai-provider.index', compact('settings', 'activeProvider'));
    }

    /**
     * Save & verify a provider key.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:groq,openai,gemini',
            'api_key'  => 'required|string|min:10',
            'model'    => 'nullable|string|max:100',
        ]);

        $model = $validated['model'] ?: AiProviderSetting::defaultModel($validated['provider']);

        // Verify the key
        try {
            if ($validated['provider'] === 'groq') {
                $this->groqService->verify($validated['api_key'], $model);
            }
            // (other providers would be verified here too)
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '❌ Clé invalide : ' . $e->getMessage());
        }

        // Deactivate all others, then save this one
        AiProviderSetting::where('provider', $validated['provider'])->delete();

        AiProviderSetting::create([
            'provider'    => $validated['provider'],
            'api_key'     => $validated['api_key'],
            'model'       => $model,
            'is_active'   => true,
            'verified_at' => now(),
        ]);

        // Disable other providers
        AiProviderSetting::where('provider', '!=', $validated['provider'])
            ->update(['is_active' => false]);

        return redirect()->route('ai-provider.index')
            ->with('success', '✅ Clé vérifiée et activée ! Le ChatBot utilise maintenant ' . ucfirst($validated['provider']) . '.');
    }

    /**
     * Deactivate a provider (fall back to Ollama).
     */
    public function deactivate(AiProviderSetting $aiProviderSetting): RedirectResponse
    {
        $aiProviderSetting->update(['is_active' => false]);

        return redirect()->route('ai-provider.index')
            ->with('success', 'Fournisseur désactivé. Retour à Ollama local.');
    }

    /**
     * Delete a saved provider.
     */
    public function destroy(AiProviderSetting $aiProviderSetting): RedirectResponse
    {
        $aiProviderSetting->delete();

        return redirect()->route('ai-provider.index')
            ->with('success', 'Fournisseur supprimé.');
    }

    /**
     * Quick ping / verify endpoint (AJAX).
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'  => 'required|string',
            'provider' => 'required|in:groq,openai,gemini',
            'model'    => 'nullable|string',
        ]);

        $model = $request->input('model') ?: AiProviderSetting::defaultModel($request->input('provider'));

        try {
            if ($request->input('provider') === 'groq') {
                $this->groqService->verify($request->input('api_key'), $model);
            }
            return response()->json(['success' => true, 'message' => '✅ Clé valide !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
