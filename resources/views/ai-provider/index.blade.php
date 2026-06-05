@extends('layouts.app')

@section('title', 'Moteur IA')
@section('page-title', 'Moteur IA')
@section('page-subtitle', 'Configurez votre fournisseur d\'Intelligence Artificielle.')

@section('content')
<div class="p-6 space-y-6">

    {{-- Active Provider Banner --}}
    @if($activeProvider)
    <div class="glass rounded-2xl p-5 border border-emerald-500/30 bg-emerald-500/5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
            </div>
            <div>
                <p class="text-emerald-300 font-semibold text-sm">Moteur actif : {{ ucfirst($activeProvider->provider) }}</p>
                <p class="text-emerald-400/70 text-xs mt-0.5">Modèle : {{ $activeProvider->model }} · Clé : {{ $activeProvider->masked_key }} · Vérifié {{ $activeProvider->verified_at?->diffForHumans() }}</p>
            </div>
        </div>
        <form action="{{ route('ai-provider.deactivate', $activeProvider) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:text-red-400 hover:border-red-500/50 transition-all">
                Désactiver (retour Ollama)
            </button>
        </form>
    </div>
    @else
    <div class="glass rounded-2xl p-5 border border-amber-500/30 bg-amber-500/5 flex items-center gap-4">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-amber-300 text-sm">Aucun fournisseur cloud actif — Le ChatBot utilise <strong>Ollama local</strong>. Ajoutez une clé API ci-dessous pour passer au cloud.</p>
    </div>
    @endif

    {{-- Add / Configure Provider Form --}}
    <div class="glass rounded-2xl border border-slate-800/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 bg-slate-900/40">
            <h2 class="text-sm font-semibold text-slate-200">Configurer un fournisseur cloud</h2>
            <p class="text-xs text-slate-500 mt-0.5">La clé sera testée avant d'être enregistrée.</p>
        </div>
        <form action="{{ route('ai-provider.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- Provider Selector --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="provider-cards">
                @foreach(App\Models\AiProviderSetting::providerLabels() as $key => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="provider" value="{{ $key }}" class="sr-only peer" {{ old('provider', 'groq') === $key ? 'checked' : '' }} onchange="updateModel(this.value)">
                    <div class="peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:text-indigo-300 border border-slate-700 rounded-xl p-4 text-sm text-slate-400 hover:border-slate-600 transition-all">
                        <div class="font-semibold text-slate-300 peer-checked:text-indigo-300">{{ ucfirst($key) }}</div>
                        <div class="text-xs mt-1 text-slate-500">
                            @if($key === 'groq') ⚡ Gratuit & ultra-rapide
                            @elseif($key === 'openai') 💎 GPT-4o (Payant)
                            @else 🌐 Google Gemini (Gratuit)
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- API Key --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Clé API</label>
                <div class="flex gap-2">
                    <input type="password" name="api_key" id="api_key_input"
                        placeholder="Collez votre clé ici (ex: gsk_...)"
                        class="flex-1 bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                        value="{{ old('api_key') }}"
                    >
                    <button type="button" id="verify-btn" onclick="verifyKey()"
                        class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-sm hover:border-indigo-500/50 hover:text-indigo-300 transition-all whitespace-nowrap">
                        Tester la clé
                    </button>
                </div>
                <div id="verify-result" class="mt-2 text-xs hidden"></div>
            </div>

            {{-- Model --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Modèle <span class="text-slate-600">(optionnel — laissez vide pour le défaut)</span></label>
                <input type="text" name="model" id="model_input"
                    placeholder="llama-3.3-70b-versatile"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                    value="{{ old('model') }}"
                >
                <p class="text-xs text-slate-600 mt-1">Groq: <code class="text-indigo-400">llama-3.3-70b-versatile</code> · OpenAI: <code class="text-indigo-400">gpt-4o-mini</code> · Gemini: <code class="text-indigo-400">gemini-1.5-flash</code></p>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/20">
                    Vérifier &amp; Activer
                </button>
            </div>
        </form>
    </div>

    {{-- Saved Providers --}}
    @if($settings->isNotEmpty())
    <div class="glass rounded-2xl border border-slate-800/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 bg-slate-900/40">
            <h2 class="text-sm font-semibold text-slate-200">Fournisseurs enregistrés</h2>
        </div>
        <table class="w-full text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">Fournisseur</th>
                    <th class="px-6 py-3 text-left">Modèle</th>
                    <th class="px-6 py-3 text-left">Clé</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($settings as $setting)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-200">{{ ucfirst($setting->provider) }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-indigo-400">{{ $setting->model }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $setting->masked_key }}</td>
                    <td class="px-6 py-4">
                        @if($setting->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactif
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        @if(!$setting->is_active)
                        <form action="{{ route('ai-provider.deactivate', $setting) }}" method="POST" class="inline">
                            @csrf
                            <button class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Activer</button>
                        </form>
                        @endif
                        <form action="{{ route('ai-provider.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs font-medium">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

<script>
const modelDefaults = {
    groq:   'llama-3.3-70b-versatile',
    openai: 'gpt-4o-mini',
    gemini: 'gemini-1.5-flash',
};

function updateModel(provider) {
    document.getElementById('model_input').placeholder = modelDefaults[provider] || '';
}

async function verifyKey() {
    const btn = document.getElementById('verify-btn');
    const resultDiv = document.getElementById('verify-result');
    const apiKey = document.getElementById('api_key_input').value.trim();
    const provider = document.querySelector('input[name="provider"]:checked')?.value || 'groq';
    const model = document.getElementById('model_input').value.trim() || modelDefaults[provider];

    if (!apiKey) {
        resultDiv.innerHTML = '<span class="text-amber-400">⚠️ Entrez une clé API d\'abord.</span>';
        resultDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Test en cours…';
    resultDiv.innerHTML = '';
    resultDiv.classList.add('hidden');

    try {
        const res = await fetch('{{ route("ai-provider.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ api_key: apiKey, provider, model }),
        });
        const data = await res.json();
        resultDiv.innerHTML = res.ok
            ? `<span class="text-emerald-400">${data.message}</span>`
            : `<span class="text-red-400">❌ ${data.message}</span>`;
    } catch (e) {
        resultDiv.innerHTML = '<span class="text-red-400">❌ Erreur réseau.</span>';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tester la clé';
        resultDiv.classList.remove('hidden');
    }
}
</script>
@endsection
