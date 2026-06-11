@extends('layouts.app')

@section('title', 'Moteur IA')
@section('page-title', 'Moteur IA')
@section('page-subtitle', 'Gérez et activez vos fournisseurs d\'Intelligence Artificielle.')

@section('content')
<div class="page">

    @if(session('success'))
    <div class="flash-message flash-message--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-message flash-message--error">{{ session('error') }}</div>
    @endif

    @if($activeProvider)
    <div class="status-banner status-banner--success">
        <div class="flex items-center gap-4">
            <div class="status-banner__icon">
                @if($activeProvider->provider === 'groq') ⚡
                @elseif($activeProvider->provider === 'openai') 💎
                @else 🌐
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-sm text-success">Moteur actif : {{ ucfirst($activeProvider->provider) }}</p>
                    <span class="badge badge--success"><span class="badge__dot"></span> EN LIGNE</span>
                </div>
                <p class="form-hint">
                    Modèle : <span class="font-mono text-success">{{ $activeProvider->model }}</span>
                    · Clé : <span class="font-mono">{{ $activeProvider->masked_key }}</span>
                    · Vérifié {{ $activeProvider->verified_at?->diffForHumans() }}
                </p>
            </div>
        </div>
        <form action="{{ route('ai-provider.deactivate', $activeProvider) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">Désactiver → Ollama</button>
        </form>
    </div>
    @else
    <div class="status-banner status-banner--warning">
        <svg class="icon-md text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-warning">Aucun fournisseur cloud actif — Le ChatBot utilise <strong>Ollama local</strong>. Ajoutez une clé API ci-dessous pour passer au cloud.</p>
    </div>
    @endif

    @if($settings->isNotEmpty())
    <div>
        <h2 class="form-section__title mb-4">Fournisseurs enregistrés</h2>
        <div class="provider-card-grid">
            @foreach($settings as $setting)
            @php
                $icon = match($setting->provider) { 'groq' => '⚡', 'openai' => '💎', default => '🌐' };
            @endphp
            <div class="provider-card {{ $setting->is_active ? 'is-active' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $icon }}</span>
                        <div>
                            <p class="data-table__primary">{{ ucfirst($setting->provider) }}</p>
                            <p class="font-mono text-xs text-accent">{{ $setting->model }}</p>
                        </div>
                    </div>
                    @if($setting->is_active)
                    <span class="badge badge--success"><span class="badge__dot"></span> ACTIF</span>
                    @else
                    <span class="badge badge--neutral"><span class="badge__dot"></span> INACTIF</span>
                    @endif
                </div>
                <div class="provider-card__key">{{ $setting->masked_key }}</div>
                <div class="flex items-center gap-2">
                    @if(!$setting->is_active)
                    <form action="{{ route('ai-provider.activate', $setting) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">⚡ Activer</button>
                    </form>
                    @else
                    <form action="{{ route('ai-provider.deactivate', $setting) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">Désactiver</button>
                    </form>
                    @endif
                    <form action="{{ route('ai-provider.destroy', $setting) }}" method="POST" onsubmit="return confirm('Supprimer ce fournisseur et sa clé ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action btn-action--danger" title="Supprimer">
                            <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="panel">
        <div class="panel__body" style="border-bottom:1px solid var(--color-border-light);">
            <h2 class="form-section__title" style="margin:0;">Ajouter / Mettre à jour un fournisseur</h2>
            <p class="form-hint">La clé sera testée avant d'être enregistrée. Vos clés existantes ne seront pas supprimées.</p>
        </div>
        <form action="{{ route('ai-provider.store') }}" method="POST" class="panel__body space-y-4">
            @csrf

            <div class="form-group">
                <label class="form-label">Fournisseur</label>
                <div class="provider-grid">
                    @foreach(['groq' => ['label' => 'Groq', 'icon' => '⚡', 'desc' => 'Gratuit & ultra-rapide (Llama 3)'], 'openai' => ['label' => 'OpenAI', 'icon' => '💎', 'desc' => 'GPT-4o · Payant'], 'gemini' => ['label' => 'Gemini', 'icon' => '🌐', 'desc' => 'Google Gemini · Gratuit']] as $key => $info)
                    <label class="provider-option">
                        <input type="radio" name="provider" value="{{ $key }}" {{ old('provider', 'groq') === $key ? 'checked' : '' }} onchange="updateModel(this.value)">
                        <div class="provider-option__card">
                            <div class="flex items-center gap-2 mb-1">
                                <span>{{ $info['icon'] }}</span>
                                <span class="font-semibold text-sm">{{ $info['label'] }}</span>
                            </div>
                            <div class="text-xs text-muted">{{ $info['desc'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Clé API</label>
                <div class="flex gap-2">
                    <input type="password" name="api_key" id="api_key_input" placeholder="Collez votre clé ici (ex: gsk_...)" class="form-control flex-1" value="{{ old('api_key') }}">
                    <button type="button" id="verify-btn" onclick="verifyKey()" class="btn btn-secondary btn-sm shrink-0">Tester la clé</button>
                </div>
                <div id="verify-result" class="form-hint hidden"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Modèle <span class="text-muted">(optionnel)</span></label>
                <input type="text" name="model" id="model_input" placeholder="llama-3.3-70b-versatile" class="form-control" value="{{ old('model') }}">
                <p class="form-hint">
                    Groq: <code class="text-accent">llama-3.3-70b-versatile</code> ·
                    OpenAI: <code class="text-accent">gpt-4o-mini</code> ·
                    Gemini: <code class="text-accent">gemini-3.5-flash</code>
                </p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary hover-lift">Vérifier &amp; Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
const modelDefaults = {
    groq:   'llama-3.3-70b-versatile',
    openai: 'gpt-4o-mini',
    gemini: 'gemini-3.5-flash',
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
        resultDiv.innerHTML = '<span class="text-warning">Entrez une clé API d\'abord.</span>';
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
            ? `<span class="text-success">${data.message}</span>`
            : `<span class="text-danger">❌ ${data.message}</span>`;
    } catch (e) {
        resultDiv.innerHTML = '<span class="text-danger">❌ Erreur réseau.</span>';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tester la clé';
        resultDiv.classList.remove('hidden');
    }
}
</script>
@endsection
