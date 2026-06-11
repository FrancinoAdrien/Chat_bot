@extends('layouts.app')

@section('title', $user->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')
@section('page-title', $user->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')

@section('content')
<div class="page" style="max-width: 42rem;">
    <div class="panel panel__body animate-fade-in-up">
        <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="form-control">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="form-control">
                    @error('prenom') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Matricule <span class="text-danger">*</span></label>
                    <input type="text" name="matricule" required value="{{ old('matricule', $user->matricule) }}" class="form-control uppercase">
                    @error('matricule') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Poste</label>
                    <input type="text" name="poste" value="{{ old('poste', $user->poste) }}" class="form-control">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select name="role" required class="form-control">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Utilisateur</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>
                <div class="form-group flex items-center" style="padding-top:1.75rem;">
                    <label class="flex items-center gap-3" style="cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->exists ? $user->is_active : true) ? 'checked' : '' }} class="form-control" style="width:auto;">
                        <span class="text-sm font-medium">Compte Actif</span>
                    </label>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section__title">Mot de passe</h3>

                @if($user->exists)
                    <div class="form-group">
                        <label class="flex items-center gap-3" style="cursor:pointer;">
                            <input type="checkbox" name="reset_password" id="reset_password" value="1" onchange="togglePasswordField()" style="width:auto;">
                            <span class="text-sm text-muted">Générer un nouveau mot de passe</span>
                        </label>
                    </div>
                @else
                    <p class="form-hint mb-4">Si vous laissez le champ vide, un mot de passe sécurisé sera généré automatiquement.</p>
                @endif

                <div id="password_field" class="form-group {{ $user->exists ? 'hidden' : '' }}">
                    <input type="text" name="custom_password" placeholder="Mot de passe personnalisé (optionnel, min 8 caractères)" class="form-control">
                    @error('custom_password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="modal__footer" style="padding-top:1.5rem;">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary hover-lift">
                    {{ $user->exists ? 'Mettre à jour' : 'Créer l\'utilisateur' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordField() {
    const checkbox = document.getElementById('reset_password');
    const field = document.getElementById('password_field');
    if (checkbox && checkbox.checked) {
        field.classList.remove('hidden');
    } else {
        field.classList.add('hidden');
    }
}
</script>
@endsection
