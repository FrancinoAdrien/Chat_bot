@extends('layouts.app')

@section('title', 'Gestion des Outils')
@section('page-title', 'Outils du ChatBot')
@section('page-subtitle', 'Configurez les requêtes API que l\'IA peut utiliser.')

@section('header-actions')
    <a href="{{ route('tools.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-500 transition-all font-medium">
        + Nouvel outil
    </a>
@endsection

@section('content')
<div class="p-6">
    @if($connections->isEmpty())
    <div class="glass rounded-xl p-6 border border-amber-500/30 bg-amber-500/5 mb-6 flex items-start gap-4">
        <svg class="w-6 h-6 text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="text-amber-300 font-medium mb-1">Aucune connexion API disponible</h4>
            <p class="text-amber-200/70 text-sm mb-3">Vous devez d'abord créer une connexion pour pouvoir y associer des outils.</p>
            <a href="{{ route('connections.index') }}" class="text-amber-400 text-sm font-medium hover:text-amber-300 transition-colors">
                Créer une connexion →
            </a>
        </div>
    </div>
    @else
    <div class="glass rounded-2xl overflow-hidden border border-slate-800/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/50 border-b border-slate-800/50">
                    <tr>
                        <th class="px-6 py-4">Outil</th>
                        <th class="px-6 py-4">Connexion API</th>
                        <th class="px-6 py-4">Endpoint</th>
                        <th class="px-6 py-4">Mots-clés</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($tools as $tool)
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-200">{{ $tool->label }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $tool->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                    {{ $tool->apiConnection->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                <span class="text-indigo-400 font-semibold">{{ $tool->method }}</span> {{ $tool->endpoint }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($tool->keywords as $kw)
                                        <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] text-slate-300">{{ $kw }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($tool->active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="testTool({{ $tool->id }}, '{{ $tool->name }}')" class="text-emerald-400 hover:text-emerald-300 transition-colors text-sm font-medium mr-2">Voir</button>
                                <a href="{{ route('tools.edit', $tool) }}" class="text-indigo-400 hover:text-indigo-300 transition-colors text-sm font-medium">Modifier</a>
                                <form action="{{ route('tools.destroy', $tool) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer cet outil ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 transition-colors text-sm font-medium">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Aucun outil configuré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tools->hasPages())
        <div class="px-6 py-4 border-t border-slate-800/50 bg-slate-900/30">
            {{ $tools->links() }}
        </div>
        @endif
    </div>
    @endif

    <!-- Modale Test API -->
    <div id="testModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[80vh]">
            <div class="flex justify-between items-center p-4 border-b border-slate-800">
                <h3 class="text-lg font-semibold text-slate-200" id="testModalTitle">Test de l'Outil</h3>
                <button onclick="closeTestModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto flex-1 bg-slate-950/50">
                <div id="testModalLoader" class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="ml-3 text-slate-400">Requête en cours...</span>
                </div>
                <div id="testModalError" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-lg mb-4 text-sm"></div>
                <pre id="testModalResult" class="hidden text-xs text-emerald-400 font-mono bg-slate-900 border border-slate-800 p-4 rounded-lg overflow-x-auto whitespace-pre-wrap"></pre>
            </div>
            <div class="p-4 border-t border-slate-800 flex justify-end">
                <button onclick="closeTestModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm transition-colors">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
function testTool(toolId, toolName) {
    const modal = document.getElementById('testModal');
    const title = document.getElementById('testModalTitle');
    const loader = document.getElementById('testModalLoader');
    const errorBox = document.getElementById('testModalError');
    const resultBox = document.getElementById('testModalResult');

    // Reset UI
    title.innerText = "Test de l'outil : " + toolName;
    loader.classList.remove('hidden');
    errorBox.classList.add('hidden');
    resultBox.classList.add('hidden');
    resultBox.innerText = '';
    
    // Show Modal
    modal.classList.remove('hidden');

    // Fetch API
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
        .catch(err => {
            loader.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorBox.innerText = "Erreur réseau ou du serveur.";
        });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}
</script>
@endsection
