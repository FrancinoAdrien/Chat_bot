@extends('layouts.app')

@section('title', 'Directives IA')
@section('page-title', 'Règles et Directives IA')
@section('page-subtitle', 'Définissez des règles strictes que l\'IA doit respecter selon l\'utilisateur.')

@section('header-actions')
    <button onclick="document.getElementById('rule-modal').classList.remove('hidden')" 
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-lg shadow-indigo-500/20 transition-all">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nouvelle Règle
    </button>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Rules List -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($rules as $rule)
            <div class="glass rounded-xl border {{ $rule->is_active ? 'border-indigo-500/30' : 'border-slate-800/50' }} p-5 flex flex-col transition-all hover:bg-slate-800/30">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-200">{{ $rule->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Cible : 
                            @if($rule->target_type === 'all')
                                <span class="px-2 py-0.5 rounded bg-slate-700 text-slate-300">Tout le monde</span>
                            @elseif($rule->target_type === 'poste')
                                <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300">Poste : {{ $rule->target_value }}</span>
                            @elseif($rule->target_type === 'user')
                                @php $u = $users->firstWhere('id', $rule->target_value); @endphp
                                <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300">Utilisateur : {{ $u ? $u->name : 'Inconnu' }}</span>
                            @endif
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer" title="Activer / Désactiver">
                        <input type="checkbox" onchange="toggleRule({{ $rule->id }})" class="sr-only peer" {{ $rule->is_active ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-300 after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-500"></div>
                    </label>
                </div>
                
                <div class="flex-1 bg-slate-900/50 rounded-lg p-3 border border-slate-800/50">
                    <p class="text-xs text-slate-300 font-mono italic">« {{ $rule->instruction }} »</p>
                </div>

                <div class="flex gap-2 mt-4 pt-4 border-t border-slate-800/50">
                    <button onclick="editRule({{ $rule }})" class="flex-1 text-xs py-2 text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded transition-colors">
                        Modifier
                    </button>
                    <form action="{{ route('ai-rules.destroy', $rule) }}" method="POST" class="flex-1" onsubmit="return confirm('Supprimer cette règle ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-xs py-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass rounded-xl border border-slate-800/50 p-12 text-center">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-slate-400 text-sm">Aucune directive définie.</p>
                <p class="text-slate-500 text-xs mt-1">Créez des règles pour imposer des limites ou des comportements spécifiques à l'IA.</p>
            </div>
        @endforelse
    </div>

</div>

<!-- Create / Edit Modal -->
<div id="rule-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
    <div class="glass w-full max-w-lg rounded-2xl border border-slate-800/80 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 flex justify-between items-center bg-slate-900/50">
            <h3 class="text-lg font-semibold text-slate-100" id="modal-title">Nouvelle Directive IA</h3>
            <button onclick="closeModal()" class="text-slate-500 hover:text-slate-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="rule-form" action="{{ route('ai-rules.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom de la règle</label>
                <input type="text" name="name" id="rule-name" required placeholder="Ex: Censure ID Produit"
                       class="w-full bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Consigne exacte (Prompt)</label>
                <textarea name="instruction" id="rule-instruction" required rows="3" placeholder="Ex: Ne révèle jamais l'ID d'un produit, dis qu'il est confidentiel."
                          class="w-full bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Cible</label>
                    <select name="target_type" id="rule-target-type" onchange="toggleTargetValue()"
                            class="w-full bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">Tout le monde</option>
                        <option value="poste">Un poste spécifique</option>
                        <option value="user">Un utilisateur spécifique</option>
                    </select>
                </div>
                
                <div id="target-value-container" class="hidden">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Valeur de la cible</label>
                    
                    <select name="target_value" id="rule-target-poste" disabled class="hidden w-full bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($postes as $poste)
                            <option value="{{ $poste }}">{{ $poste }}</option>
                        @endforeach
                    </select>
                    
                    <select name="target_value" id="rule-target-user" disabled class="hidden w-full bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="rule-active" value="1" checked class="rounded border-slate-700 bg-slate-900 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900">
                <label for="rule-active" class="text-sm text-slate-300">Activer cette règle immédiatement</label>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function closeModal() {
        document.getElementById('rule-modal').classList.add('hidden');
        document.getElementById('rule-form').reset();
        document.getElementById('form-method').value = 'POST';
        document.getElementById('rule-form').action = "{{ route('ai-rules.store') }}";
        document.getElementById('modal-title').textContent = 'Nouvelle Directive IA';
        toggleTargetValue();
    }

    function editRule(rule) {
        document.getElementById('modal-title').textContent = 'Modifier la Directive';
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('rule-form').action = `/ai-rules/${rule.id}`;
        
        document.getElementById('rule-name').value = rule.name;
        document.getElementById('rule-instruction').value = rule.instruction;
        document.getElementById('rule-target-type').value = rule.target_type;
        document.getElementById('rule-active').checked = rule.is_active;
        
        toggleTargetValue();
        
        if (rule.target_type === 'poste') {
            document.getElementById('rule-target-poste').value = rule.target_value;
        } else if (rule.target_type === 'user') {
            document.getElementById('rule-target-user').value = rule.target_value;
        }

        document.getElementById('rule-modal').classList.remove('hidden');
    }

    function toggleTargetValue() {
        const type = document.getElementById('rule-target-type').value;
        const container = document.getElementById('target-value-container');
        const posteSelect = document.getElementById('rule-target-poste');
        const userSelect = document.getElementById('rule-target-user');

        if (type === 'all') {
            container.classList.add('hidden');
            posteSelect.disabled = true;
            userSelect.disabled = true;
            posteSelect.classList.add('hidden');
            userSelect.classList.add('hidden');
        } else if (type === 'poste') {
            container.classList.remove('hidden');
            posteSelect.disabled = false;
            userSelect.disabled = true;
            posteSelect.classList.remove('hidden');
            userSelect.classList.add('hidden');
        } else if (type === 'user') {
            container.classList.remove('hidden');
            posteSelect.disabled = true;
            userSelect.disabled = false;
            posteSelect.classList.add('hidden');
            userSelect.classList.remove('hidden');
        }
    }

    async function toggleRule(id) {
        try {
            await fetch(`/ai-rules/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            window.location.reload();
        } catch (e) {
            console.error(e);
        }
    }
</script>
@endpush
