@extends('layouts.app')

@section('title', 'Directives IA')
@section('page-title', 'Règles et Directives IA')
@section('page-subtitle', 'Définissez des règles strictes que l\'IA doit respecter selon l\'utilisateur.')

@section('header-actions')
    <button type="button" onclick="openRuleModal()" class="btn btn-primary btn-sm hover-lift">
        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nouvelle Règle
    </button>
@endsection

@section('content')
<div class="page">

    <form method="GET" class="filter-bar filter-bar--grid cols-3 panel panel__body">
        <div>
            <label class="form-label">Rechercher une directive</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Nom, instruction, cible..." class="form-control form-control--search" />
        </div>
        <div>
            <label class="form-label">Filtrer par cible</label>
            <select name="target" class="form-control">
                <option value="">Toutes les cibles</option>
                <option value="all" {{ (isset($targetType) && $targetType === 'all') ? 'selected' : '' }}>Tout le monde</option>
                <option value="poste" {{ (isset($targetType) && $targetType === 'poste') ? 'selected' : '' }}>Poste</option>
                <option value="user" {{ (isset($targetType) && $targetType === 'user') ? 'selected' : '' }}>Utilisateur</option>
            </select>
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
        </div>
    </form>

    @if(session('success'))
        <div class="flash-message flash-message--success">{{ session('success') }}</div>
    @endif

    <div class="rule-grid">
        @forelse($rules as $rule)
            <div class="rule-card {{ $rule->is_active ? 'is-active' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="data-table__primary">{{ $rule->name }}</h3>
                        <p class="data-table__secondary mt-1">
                            Cible :
                            @if($rule->target_type === 'all')
                                <span class="badge badge--neutral">Tout le monde</span>
                            @elseif($rule->target_type === 'poste')
                                <span class="badge badge--accent">Poste : {{ $rule->target_value }}</span>
                            @elseif($rule->target_type === 'user')
                                @php $u = $users->firstWhere('id', $rule->target_value); @endphp
                                <span class="badge badge--purple">Utilisateur : {{ $u ? $u->name : 'Inconnu' }}</span>
                            @endif
                        </p>
                    </div>
                    <label class="toggle" title="Activer / Désactiver">
                        <input type="checkbox" onchange="toggleRule({{ $rule->id }})" {{ $rule->is_active ? 'checked' : '' }}>
                        <span class="toggle__track"></span>
                    </label>
                </div>

                <div class="rule-card__quote">« {{ $rule->instruction }} »</div>

                <div class="rule-card__footer">
                    <button type="button" onclick="editRule({{ $rule->toJson() }})" class="btn btn-ghost btn-sm flex-1">Modifier</button>
                    <form action="{{ route('ai-rules.destroy', $rule) }}" method="POST" class="flex-1" onsubmit="return confirm('Supprimer cette règle ?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm flex-1 text-danger" style="width:100%;">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-state__icon">
                    <svg class="icon-lg text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="empty-state__text">Aucune directive définie.</p>
                <p class="form-hint">Créez des règles pour imposer des limites ou des comportements spécifiques à l'IA.</p>
            </div>
        @endforelse
    </div>
</div>

<div id="rule-modal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal__body">
            <h2 class="modal__title" id="modal-title">Nouvelle Directive IA</h2>
            <form id="rule-form" action="{{ route('ai-rules.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">

                <div class="form-group">
                    <label class="form-label">Nom de la règle</label>
                    <input type="text" name="name" id="rule-name" required placeholder="Ex: Censure ID Produit" class="form-control form-control--sm">
                </div>

                <div class="form-group">
                    <label class="form-label">Consigne exacte (Prompt)</label>
                    <textarea name="instruction" id="rule-instruction" required rows="3" placeholder="Ex: Ne révèle jamais l'ID d'un produit, dis qu'il est confidentiel." class="form-control form-control--sm"></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Cible</label>
                        <select name="target_type" id="rule-target-type" onchange="toggleTargetValue()" class="form-control form-control--sm">
                            <option value="all">Tout le monde</option>
                            <option value="poste">Un poste spécifique</option>
                            <option value="user">Un utilisateur spécifique</option>
                        </select>
                    </div>
                    <div id="target-value-container" class="form-group hidden">
                        <label class="form-label">Valeur de la cible</label>
                        <select name="target_value" id="rule-target-poste" disabled class="form-control form-control--sm hidden">
                            @foreach($postes as $poste)
                                <option value="{{ $poste }}">{{ $poste }}</option>
                            @endforeach
                        </select>
                        <select name="target_value" id="rule-target-user" disabled class="form-control form-control--sm hidden">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="rule-active" value="1" checked style="width:auto;">
                    <label for="rule-active" class="text-sm">Activer cette règle immédiatement</label>
                </div>

                <div class="modal__footer">
                    <button type="button" onclick="closeModal()" class="btn btn-ghost btn-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRuleModal() {
    document.getElementById('rule-modal').classList.remove('hidden');
}

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

document.getElementById('rule-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
