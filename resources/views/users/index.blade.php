@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('page-title', 'Utilisateurs')
@section('page-subtitle', 'Gérez les accès et les permissions au ChatBot.')

@section('header-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm hover-lift">
        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nouvel Utilisateur
    </a>
@endsection

@section('content')
<div class="page">
    <div class="filter-bar panel panel__body">
        <div>
            <label class="form-label">Recherche d'utilisateur</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" form="users-search" placeholder="Nom, prénom, matricule, poste..."
                class="form-control form-control--search" />
        </div>
        <p class="filter-bar__hint">Filtrer les utilisateurs par nom ou poste pour les retrouver plus vite.</p>
    </div>

    <form id="users-search" method="GET"></form>

    <div class="panel data-table-wrap animate-fade-in-up">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Matricule</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th class="data-table__actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="data-table__primary">{{ $user->name }} {{ $user->prenom }}</div>
                        <div class="data-table__secondary">{{ $user->poste ?? 'Aucun poste' }}</div>
                    </td>
                    <td class="font-mono text-accent">{{ $user->matricule }}</td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge badge--purple">Admin</span>
                        @else
                            <span class="badge badge--neutral">Utilisateur</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge--success"><span class="badge__dot"></span> Actif</span>
                        @else
                            <span class="badge badge--danger"><span class="badge__dot"></span> Inactif</span>
                        @endif
                    </td>
                    <td class="data-table__actions">
                        <a href="{{ route('users.edit', $user) }}" class="link-action">Modifier</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="link-action link-action--danger" style="background:none;border:none;cursor:pointer;padding:0;">Supprimer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
