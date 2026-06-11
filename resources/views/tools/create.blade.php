@extends('layouts.app')

@section('title', 'Créer un Outil')
@section('page-title', 'Nouvel Outil')
@section('page-subtitle', 'Ajoutez une source de données à l\'IA : API distante ou fichier Excel.')

@section('header-actions')
    <a href="{{ route('tools.index') }}" class="btn btn-secondary btn-sm">&larr; Retour</a>
@endsection

@section('content')
<div class="page" style="max-width: 56rem;">
    <div class="panel panel__body">

        <div class="form-group mb-4">
            <label class="form-label">Type de source de données</label>
            <div class="tabs" style="max-width:24rem;">
                <button type="button" id="btn-api" onclick="switchType('api')" class="tab is-active">🌐 API Distante</button>
                <button type="button" id="btn-excel" onclick="switchType('excel')" class="tab">📊 Fichier Excel</button>
            </div>
        </div>

        <form id="form-api" action="{{ route('tools.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="api">
            @if($connections->isEmpty())
                <div class="empty-state">
                    <p class="empty-state__text">Vous devez d'abord configurer une connexion API.</p>
                    <a href="{{ route('connections.index') }}" class="link-action">+ Créer une connexion</a>
                </div>
            @else
            <div class="form-grid-2">
                <div class="form-group span-2">
                    <label class="form-label">Connexion API *</label>
                    <select name="api_connection_id" required class="form-control">
                        <option value="">Sélectionner une connexion</option>
                        @foreach($connections as $c)
                            <option value="{{ $c->id }}" {{ old('api_connection_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('api_connection_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nom interne * <span class="text-muted text-xs">(ex: getSales)</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Libellé * <span class="text-muted text-xs">(ex: Ventes du jour)</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required class="form-control">
                    @error('label')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Endpoint API *</label>
                    <div class="input-group">
                        <span class="input-group__addon">GET/POST</span>
                        <input type="text" name="endpoint" value="{{ old('endpoint') }}" placeholder="/api/endpoint" required class="form-control">
                    </div>
                    @error('endpoint')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Méthode HTTP *</label>
                    <select name="method" required class="form-control">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés déclencheurs * <span class="text-muted text-xs">(virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords') }}" placeholder="ventes, ca, chiffre" required class="form-control">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Description <span class="text-muted text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="form-group span-2 flex items-center gap-3 form-section" style="margin-top:0;padding-top:1rem;">
                    <input type="checkbox" name="active" id="active-api" value="1" {{ old('active', true) ? 'checked' : '' }} style="width:auto;">
                    <label for="active-api" class="text-sm" style="cursor:pointer;">Activer cet outil</label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary hover-lift">Créer l'outil API</button>
            </div>
            @endif
        </form>

        <form id="form-excel" action="{{ route('tools.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="hidden" name="type" value="excel">

            <div class="drop-zone" id="drop-zone">
                <svg class="icon-lg text-accent" style="margin:0 auto 0.75rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                </svg>
                <p class="font-medium mb-1">Glissez votre fichier ici</p>
                <p class="form-hint mb-4">Formats : .xlsx, .xls, .csv — Max 10 Mo</p>
                <label class="btn btn-primary btn-sm" style="cursor:pointer;">
                    Parcourir…
                    <input type="file" name="excel_file" id="excel-file" accept=".xlsx,.xls,.csv" required class="hidden" onchange="onFileSelected(this)">
                </label>
            </div>

            <div id="file-info" class="file-info-banner hidden">
                <svg class="icon-md text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm text-success" id="file-name">—</p>
                    <p class="form-hint">Chaque feuille du fichier sera créée comme un outil indépendant.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group span-2">
                    <label class="form-label">Mots-clés déclencheurs * <span class="text-muted text-xs">(virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords') }}" placeholder="ventes excel, rapport, liste" class="form-control">
                    <p class="form-hint">Ces mots-clés s'appliqueront à tous les onglets du fichier.</p>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Description <span class="text-muted text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="form-group span-2 flex items-center gap-3 form-section" style="margin-top:0;padding-top:1rem;">
                    <input type="checkbox" name="active" id="active-excel" value="1" checked style="width:auto;">
                    <label for="active-excel" class="text-sm" style="cursor:pointer;">Activer cet outil</label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary hover-lift">Importer le fichier Excel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchType(type) {
    const formApi = document.getElementById('form-api');
    const formExcel = document.getElementById('form-excel');
    const btnApi = document.getElementById('btn-api');
    const btnExcel = document.getElementById('btn-excel');
    const isApi = type === 'api';
    formApi.classList.toggle('hidden', !isApi);
    formExcel.classList.toggle('hidden', isApi);
    btnApi.classList.toggle('is-active', isApi);
    btnExcel.classList.toggle('is-active', !isApi);
}

function onFileSelected(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' Ko)';
    document.getElementById('file-info').classList.remove('hidden');
    document.getElementById('drop-zone').classList.add('is-filled');
}

const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('is-dragover'); });
dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('is-dragover'); });
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('is-dragover');
    const fileInput = document.getElementById('excel-file');
    fileInput.files = e.dataTransfer.files;
    onFileSelected(fileInput);
});
</script>
@endpush
