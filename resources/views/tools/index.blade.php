@extends('layouts.app')

@section('title', 'Gestion des Outils')
@section('page-title', 'Outils du ChatBot')
@section('page-subtitle', 'Configurez les sources de données (API ou Excel) que l\'IA peut utiliser.')

@section('header-actions')
    <a href="{{ route('tools.create') }}" class="btn btn-primary btn-sm hover-lift">+ Nouvel outil</a>
@endsection

@section('content')
<div class="page">
    <form method="GET" class="filter-bar filter-bar--grid cols-2 panel panel__body">
        <div>
            <label class="form-label">Rechercher un outil</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Nom, nom interne, mot-clé, endpoint..." class="form-control" />
        </div>
        <div>
            <label class="form-label">Connexion API</label>
            <select name="connection" class="form-control">
                <option value="">Toutes les connexions</option>
                @foreach($connections as $connection)
                    <option value="{{ $connection->name }}" {{ (isset($connectionFilter) && $connectionFilter === $connection->name) ? 'selected' : '' }}>{{ $connection->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($connections->isEmpty() && $tools->where('type','api')->count() > 0)
    <div class="alert-banner alert-banner--warning">
        <svg class="icon-md text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="alert-banner__title">Aucune connexion API disponible</h4>
            <p class="alert-banner__text">Vous devez d'abord créer une connexion pour ajouter des outils API. Vous pouvez toujours ajouter des outils Excel.</p>
            <a href="{{ route('connections.index') }}" class="link-action">Créer une connexion →</a>
        </div>
    </div>
    @endif

    <div class="panel data-table-wrap">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Outil</th>
                        <th>Type / Source</th>
                        <th>Endpoint / Feuille</th>
                        <th>Mots-clés</th>
                        <th>Statut</th>
                        <th class="data-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tools as $tool)
                        <tr>
                            <td>
                                <p class="data-table__primary">{{ $tool->label }}</p>
                                <p class="data-table__secondary">{{ $tool->name }}</p>
                            </td>
                            <td>
                                @if($tool->type === 'excel')
                                    <span class="badge badge--success">📊 Fichier Excel</span>
                                @else
                                    <span class="badge badge--accent">🌐 {{ $tool->apiConnection?->name ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-secondary">
                                @if($tool->type === 'excel')
                                    <span class="text-success font-semibold">SHEET</span> {{ $tool->sheet_name }}
                                @else
                                    <span class="text-accent font-semibold">{{ $tool->method }}</span> {{ $tool->endpoint }}
                                @endif
                            </td>
                            <td>
                                <div class="tag-list">
                                    @foreach($tool->keywords as $kw)
                                        <span class="tag">{{ $kw }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($tool->active)
                                    <span class="badge badge--success"><span class="badge__dot"></span> Actif</span>
                                @else
                                    <span class="badge badge--neutral"><span class="badge__dot"></span> Inactif</span>
                                @endif
                            </td>
                            <td class="data-table__actions">
                                <button type="button" onclick="testTool({{ $tool->id }}, '{{ addslashes($tool->label) }}')" class="link-action">Voir</button>
                                <a href="{{ route('tools.edit', $tool) }}" class="link-action">Modifier</a>
                                <form action="{{ route('tools.destroy', $tool) }}" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cet outil ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="link-action link-action--danger" style="background:none;border:none;cursor:pointer;padding:0;">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p class="empty-state__text">Aucun outil configuré pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tools->hasPages())
        <div class="panel__footer">
            {{ $tools->links() }}
        </div>
        @endif
    </div>

    <div id="testModal" class="modal-overlay hidden">
        <div class="modal modal--lg">
            <div class="modal__header">
                <h3 class="modal__title" id="testModalTitle">Test de l'Outil</h3>
                <button type="button" onclick="closeTestModal()" class="modal__close" aria-label="Fermer">
                    <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal__content">
                <div id="testModalLoader" class="flex items-center justify-center p-6">
                    <svg class="loading-spinner icon-lg text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-muted ml-3">Requête en cours...</span>
                </div>
                <div id="testModalError" class="flash-message flash-message--error hidden"></div>
                <pre id="testModalResult" class="code-block hidden"></pre>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="closeTestModal()" class="btn btn-secondary btn-sm">Fermer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testTool(toolId, toolName) {
    const modal = document.getElementById('testModal');
    const title = document.getElementById('testModalTitle');
    const loader = document.getElementById('testModalLoader');
    const errorBox = document.getElementById('testModalError');
    const resultBox = document.getElementById('testModalResult');

    title.innerText = "Test de l'outil : " + toolName;
    loader.classList.remove('hidden');
    errorBox.classList.add('hidden');
    resultBox.classList.add('hidden');
    resultBox.innerText = '';
    modal.classList.remove('hidden');

    fetch(`/tools/${toolId}/test`)
        .then(response => response.json())
        .then(data => {
            loader.classList.add('hidden');
            if (data.success) {
                resultBox.classList.remove('hidden');
                resultBox.innerText = JSON.stringify(data.data, null, 2);
            } else {
                errorBox.classList.remove('hidden');
                errorBox.innerText = data.message || "Erreur inconnue.";
            }
        })
        .catch(() => {
            loader.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorBox.innerText = "Erreur réseau ou du serveur.";
        });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

document.getElementById('testModal').addEventListener('click', function(e) {
    if (e.target === this) closeTestModal();
});
</script>
@endpush
@endsection
