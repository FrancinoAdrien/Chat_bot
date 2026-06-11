<?php

namespace App\Http\Controllers;

use App\Models\AiProviderSetting;
use App\Services\GeminiService;
use App\Services\GroqService;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiProviderController extends Controller
{
    public function __construct(
        private readonly GroqService   $groqService,
        private readonly OpenAiService $openAiService,
        private readonly GeminiService $geminiService,
    ) {}

    public function index(): View
    {
        $settings       = AiProviderSetting::orderBy('is_active', 'desc')->orderBy('provider')->get();
        $activeProvider = AiProviderSetting::activeCloud();

        return view('ai-provider.index', compact('settings', 'activeProvider'));
    }

    /**
     * Save a new provider key (without auto-activating if another is already active).
     * If no other provider is active, this one will become active automatically.
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
            $this->verifyProvider($validated['provider'], $validated['api_key'], $model);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', '❌ Clé invalide : ' . $e->getMessage());
        }

        $hasActive = AiProviderSetting::where('is_active', true)->exists();

        // Update or create an entry for this provider
        AiProviderSetting::updateOrCreate(
            ['provider' => $validated['provider']],
            [
                'api_key'     => $validated['api_key'],
                'model'       => $model,
                'is_active'   => !$hasActive, // activate only if nothing else is active
                'verified_at' => now(),
            ]
        );

        $activatedMsg = !$hasActive ? ' et activé' : '. Cliquez sur "Activer" dans la liste pour basculer vers ce fournisseur';

        return redirect()->route('ai-provider.index')
            ->with('success', '✅ Clé vérifiée' . $activatedMsg . '.');
    }

    /**
     * Activate a saved provider (deactivates all others).
     */
    public function activate(AiProviderSetting $aiProviderSetting): RedirectResponse
    {
        // Deactivate all
        AiProviderSetting::query()->update(['is_active' => false]);

        // Activate the selected one
        $aiProviderSetting->update(['is_active' => true]);

        return redirect()->route('ai-provider.index')
            ->with('success', '✅ ' . ucfirst($aiProviderSetting->provider) . ' est maintenant le moteur IA actif.');
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
            $this->verifyProvider($request->input('provider'), $request->input('api_key'), $model);
            return response()->json(['success' => true, 'message' => '✅ Clé valide !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Internally route verification to the correct service.
     */
    private function verifyProvider(string $provider, string $apiKey, string $model): void
    {
        match($provider) {
            'openai' => $this->openAiService->verify($apiKey, $model),
            'gemini' => $this->geminiService->verify($apiKey, $model),
            default  => $this->groqService->verify($apiKey, $model),
        };
    }
}
