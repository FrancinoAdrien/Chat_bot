@extends('layouts.app')

@section('title', 'Moteur IA')
@section('page-title', 'Moteur IA')
@section('page-subtitle', 'Gérez et activez vos fournisseurs d\'Intelligence Artificielle.')

@section('content')
<div class="p-6 space-y-6">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="glass rounded-xl px-4 py-3 border border-emerald-500/30 bg-emerald-500/5 text-emerald-300 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="glass rounded-xl px-4 py-3 border border-red-500/30 bg-red-500/5 text-red-300 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Active Provider Banner --}}
    @if($activeProvider)
    <div class="glass rounded-2xl p-5 border border-emerald-500/30 bg-emerald-500/5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-2xl">
                @if($activeProvider->provider === 'groq') ⚡
                @elseif($activeProvider->provider === 'openai') 💎
                @else 🌐
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-emerald-300 font-semibold text-sm">Moteur actif : {{ ucfirst($activeProvider->provider) }}</p>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> EN LIGNE
                    </span>
                </div>
                <p class="text-emerald-400/70 text-xs mt-0.5">
                    Modèle : <span class="font-mono text-emerald-300">{{ $activeProvider->model }}</span>
                    &nbsp;·&nbsp; Clé : <span class="font-mono">{{ $activeProvider->masked_key }}</span>
                    &nbsp;·&nbsp; Vérifié {{ $activeProvider->verified_at?->diffForHumans() }}
                </p>
            </div>
        </div>
        <form action="{{ route('ai-provider.deactivate', $activeProvider) }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:text-red-400 hover:border-red-500/50 transition-all">
                Désactiver → Ollama
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

    {{-- Saved Providers (cards) --}}
    @if($settings->isNotEmpty())
    <div>
        <h2 class="text-sm font-semibold text-slate-300 mb-3">Fournisseurs enregistrés</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($settings as $setting)
            @php
                $isActive = $setting->is_active;
                $icon = match($setting->provider) { 'groq' => '⚡', 'openai' => '💎', default => '🌐' };
                $color = $isActive ? 'emerald' : 'slate';
            @endphp
            <div class="glass rounded-2xl border {{ $isActive ? 'border-emerald-500/40 bg-emerald-500/5' : 'border-slate-700/50' }} p-5 flex flex-col gap-4 transition-all">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="text-2xl">{{ $icon }}</div>
                        <div>
                            <p class="font-semibold text-slate-100 text-sm">{{ ucfirst($setting->provider) }}</p>
                            <p class="text-xs font-mono text-indigo-400">{{ $setting->model }}</p>
                        </div>
                    </div>
                    @if($isActive)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> ACTIF
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-700/50 text-slate-400 border border-slate-600/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> INACTIF
                    </span>
                    @endif
                </div>

                {{-- Key info --}}
                <div class="bg-slate-900/50 rounded-lg px-3 py-2 text-xs text-slate-400 font-mono flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    {{ $setting->masked_key }}
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 mt-auto">
                    @if(!$isActive)
                    <form action="{{ route('ai-provider.activate', $setting) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-all text-center">
                            ⚡ Activer
                        </button>
                    </form>
                    @else
                    <form action="{{ route('ai-provider.deactivate', $setting) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-300 text-xs font-medium transition-all text-center">
                            Désactiver
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('ai-provider.destroy', $setting) }}" method="POST" onsubmit="return confirm('Supprimer ce fournisseur et sa clé ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-red-500/10 border border-slate-700 hover:border-red-500/30 text-slate-400 hover:text-red-400 transition-all" title="Supprimer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Add / Configure Provider Form --}}
    <div class="glass rounded-2xl border border-slate-800/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 bg-slate-900/40 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-200">Ajouter / Mettre à jour un fournisseur</h2>
                <p class="text-xs text-slate-500 mt-0.5">La clé sera testée avant d'être enregistrée. Vos clés existantes ne seront pas supprimées.</p>
            </div>
        </div>
        <form action="{{ route('ai-provider.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- Provider Selector --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-2">Fournisseur</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="provider-cards">
                    @foreach(['groq' => ['label' => 'Groq', 'icon' => '⚡', 'desc' => 'Gratuit & ultra-rapide (Llama 3)'], 'openai' => ['label' => 'OpenAI', 'icon' => '💎', 'desc' => 'GPT-4o · Payant'], 'gemini' => ['label' => 'Gemini', 'icon' => '🌐', 'desc' => 'Google Gemini · Gratuit']] as $key => $info)
                    <label class="cursor-pointer">
                        <input type="radio" name="provider" value="{{ $key }}" class="sr-only peer" {{ old('provider', 'groq') === $key ? 'checked' : '' }} onchange="updateModel(this.value)">
                        <div class="peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 border border-slate-700 rounded-xl p-4 hover:border-slate-600 transition-all">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl">{{ $info['icon'] }}</span>
                                <span class="font-semibold text-slate-200 text-sm">{{ $info['label'] }}</span>
                            </div>
                            <div class="text-xs text-slate-500">{{ $info['desc'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
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
                <p class="text-xs text-slate-600 mt-1">
                    Groq: <code class="text-indigo-400">llama-3.3-70b-versatile</code> &nbsp;·&nbsp;
                    OpenAI: <code class="text-indigo-400">gpt-4o-mini</code> &nbsp;·&nbsp;
                    Gemini: <code class="text-indigo-400">gemini-1.5-flash</code>
                </p>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/20">
                    Vérifier &amp; Enregistrer
                </button>
            </div>
        </form>
    </div>

</div>

<script>
const modelDefaults = {
    groq:   'llama-3.3-70b-versatile',
    openai: 'gpt-4o-mini',
    gemini: 'gemini-1.5-flash',
};

function updateModel(provider) {
    const input = document.getElementById('model_input');
    input.placeholder = modelDefaults[provider] || '';
    input.value = '';
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
