@extends('layouts.app')

@section('title', 'Modifier l\'Outil')
@section('page-title', 'Modifier : ' . $tool->label)
@section('page-subtitle', $tool->type === 'excel' ? '📊 Outil Excel — Feuille : ' . $tool->sheet_name : '🌐 Outil API')

@section('header-actions')
    <a href="{{ route('tools.index') }}" class="btn btn-secondary btn-sm">&larr; Retour</a>
@endsection

@section('content')
<div class="page" style="max-width: 56rem;">
    <div class="panel panel__body">

        @if($tool->type === 'excel')
        <div class="file-info-banner mb-4">
            <svg class="icon-md text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/>
            </svg>
            <div>
                <p class="text-sm text-success font-medium">Fichier : {{ basename($tool->file_path) }}</p>
                <p class="form-hint">Feuille Excel active : <span class="text-success">{{ $tool->sheet_name }}</span> — Pour remplacer entièrement un fichier, supprimez cet outil et recréez-le.</p>
            </div>
        </div>

        <form action="{{ route('tools.update', $tool) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-grid-2">
                <div class="form-group span-2">
                    <label class="form-label">Libellé *</label>
                    <input type="text" name="label" value="{{ old('label', $tool->label) }}" required class="form-control">
                    @error('label')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Mots-clés déclencheurs * <span class="text-muted text-xs">(virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords', implode(', ', $tool->keywords ?? [])) }}" required class="form-control">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Remplacer le fichier <span class="text-muted text-xs">(optionnel)</span></label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" class="form-control">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Description <span class="text-muted text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $tool->description) }}</textarea>
                </div>
                <div class="form-group span-2 flex items-center gap-3 form-section" style="margin-top:0;padding-top:1rem;">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $tool->active) ? 'checked' : '' }} style="width:auto;">
                    <label for="active" class="text-sm font-medium" style="cursor:pointer;">Activer cet outil</label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary hover-lift">Mettre à jour l'outil Excel</button>
            </div>
        </form>

        @else
        <form action="{{ route('tools.update', $tool) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid-2">
                <div class="form-group span-2">
                    <label class="form-label">Connexion API *</label>
                    <select name="api_connection_id" required class="form-control">
                        <option value="">Sélectionner une connexion</option>
                        @foreach($connections as $c)
                            <option value="{{ $c->id }}" {{ old('api_connection_id', $tool->api_connection_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('api_connection_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nom interne *</label>
                    <input type="text" name="name" value="{{ old('name', $tool->name) }}" required class="form-control">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Libellé *</label>
                    <input type="text" name="label" value="{{ old('label', $tool->label) }}" required class="form-control">
                    @error('label')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Endpoint API *</label>
                    <div class="input-group">
                        <span class="input-group__addon">GET/POST</span>
                        <input type="text" name="endpoint" value="{{ old('endpoint', $tool->endpoint) }}" placeholder="/api/endpoint" required class="form-control">
                    </div>
                    @error('endpoint')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Méthode HTTP *</label>
                    <select name="method" required class="form-control">
                        <option value="GET" {{ old('method', $tool->method) == 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ old('method', $tool->method) == 'POST' ? 'selected' : '' }}>POST</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés * <span class="text-muted text-xs">(virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords', implode(', ', $tool->keywords ?? [])) }}" required class="form-control">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Description <span class="text-muted text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $tool->description) }}</textarea>
                </div>
                <div class="form-group span-2 flex items-center gap-3 form-section" style="margin-top:0;padding-top:1rem;">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $tool->active) ? 'checked' : '' }} style="width:auto;">
                    <label for="active" class="text-sm font-medium" style="cursor:pointer;">Activer cet outil</label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary hover-lift">Mettre à jour l'outil API</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
