<!-- 
  ═══════════════════════════════════════════════════════════════════
  EXEMPLE - Mise à jour d'une vue Blade
  ═══════════════════════════════════════════════════════════════════
  
  Cet exemple montre comment convertir une page Tailwind vers
  les nouvelles classes CSS modulaires.
-->

@extends('layouts.app')

@section('title', 'Outils API')
@section('page-title', 'Gestion des Outils API')
@section('page-subtitle', 'Créez, modifiez et gérez vos outils API intégrés')

@section('header-actions')
  <a href="{{ route('tools.create') }}" class="btn btn-primary">
    ✚ Nouveau Outil
  </a>
@endsection

@section('content')

<!-- ─────────────────────────────────────────────────────────────────
     PAGE HEADER
     ───────────────────────────────────────────────────────────────── -->

<div class="page-header animate-fade-in-down">
  <h1 class="page-title">
    <span class="gradient-text">Outils API</span>
  </h1>
  <div class="page-actions">
    <a href="{{ route('tools.create') }}" class="btn btn-primary">
      ✚ Nouveau Outil
    </a>
    <button class="btn btn-secondary" onclick="exportTools()">
      📥 Exporter
    </button>
  </div>
</div>

<!-- ─────────────────────────────────────────────────────────────────
     SEARCH & FILTER BAR
     ───────────────────────────────────────────────────────────────── -->

<div class="search-filter-bar animate-slide-in-left">
  <form action="{{ route('tools.index') }}" method="GET" class="search-filter-container cols-3">
    
    <!-- Recherche Globale -->
    <div class="search-group">
      <input 
        type="text" 
        name="search" 
        placeholder="Rechercher par nom, endpoint, keywords..."
        value="{{ request('search') }}"
      >
      <span class="search-icon">🔍</span>
      @if(request('search'))
        <button type="button" class="search-clear" onclick="document.querySelector('[name=search]').value=''; document.querySelector('form').submit();">✕</button>
      @endif
    </div>

    <!-- Filtre Connexion -->
    <select name="connection" class="filter-select" onchange="this.form.submit()">
      <option value="">Toutes les connexions</option>
      @foreach($connections as $conn)
        <option value="{{ $conn->id }}" {{ request('connection') == $conn->id ? 'selected' : '' }}>
          {{ $conn->name }}
        </option>
      @endforeach
    </select>

    <!-- Bouton Rechercher -->
    <button type="submit" class="btn btn-primary">🔎 Rechercher</button>
  </form>
</div>

<!-- ─────────────────────────────────────────────────────────────────
     RESULTS INFO
     ───────────────────────────────────────────────────────────────── -->

@if($tools->count() > 0)
<div class="results-info animate-slide-in-left delay-100">
  <span class="results-count">
    Affichage <strong>{{ $tools->count() }}</strong> de <strong>{{ $tools->total() }}</strong> outils
  </span>
  @if(request('search'))
    <span class="results-count">
      Recherche: <strong>"{{ request('search') }}"</strong>
      <a href="{{ route('tools.index') }}" class="text-accent hover:underline">(Réinitialiser)</a>
    </span>
  @endif
</div>
@endif

<!-- ─────────────────────────────────────────────────────────────────
     TABLE
     ───────────────────────────────────────────────────────────────── -->

<div class="table-wrapper animate-fade-in-up">
  @if($tools->count() > 0)
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Endpoint</th>
          <th>Connexion</th>
          <th>Keywords</th>
          <th>Status</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tools as $tool)
          <tr class="{{ request('search') && (
            str_contains($tool->name, request('search')) ||
            str_contains($tool->endpoint, request('search')) ||
            str_contains($tool->keywords ?? '', request('search'))
          ) ? 'highlighted' : '' }}">
            
            <!-- Nom -->
            <td>
              <strong class="text-accent">{{ $tool->name }}</strong>
              @if(request('search') && str_contains($tool->name, request('search')))
                <span class="highlight-text">{{ request('search') }}</span>
              @endif
            </td>
            
            <!-- Endpoint -->
            <td>
              <code class="text-secondary">{{ $tool->endpoint }}</code>
            </td>
            
            <!-- Connexion -->
            <td>
              @if($tool->connection)
                <span class="cell-status status-active">
                  ✓ {{ $tool->connection->name }}
                </span>
              @else
                <span class="cell-status status-inactive">
                  − Non assignée
                </span>
              @endif
            </td>
            
            <!-- Keywords -->
            <td>
              <span class="text-muted text-xs">
                {{ $tool->keywords ?? '−' }}
              </span>
            </td>
            
            <!-- Status -->
            <td>
              @if($tool->is_active)
                <span class="cell-status status-active">
                  ✓ Actif
                </span>
              @else
                <span class="cell-status status-inactive">
                  ✕ Inactif
                </span>
              @endif
            </td>
            
            <!-- Actions -->
            <td>
              <div class="table-actions">
                <a href="{{ route('tools.show', $tool) }}" 
                   class="action-btn" 
                   title="Voir">
                  👁️
                </a>
                <a href="{{ route('tools.edit', $tool) }}" 
                   class="action-btn" 
                   title="Modifier">
                  ✎
                </a>
                <button class="action-btn delete" 
                        onclick="if(confirm('Êtes-vous sûr?')) document.querySelector('#delete-{{ $tool->id }}').submit();"
                        title="Supprimer">
                  🗑️
                </button>
                <form id="delete-{{ $tool->id }}" 
                      action="{{ route('tools.destroy', $tool) }}" 
                      method="POST" 
                      style="display: none;">
                  @csrf @method('DELETE')
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <!-- ─────────────────────────────────────────────────────────────
         PAGINATION
         ───────────────────────────────────────────────────────────── -->

    @if($tools->hasPages())
      <div class="pagination">
        {{-- Previous Page Link --}}
        @if ($tools->onFirstPage())
          <span class="pagination-item disabled">← Précédent</span>
        @else
          <a href="{{ $tools->previousPageUrl() }}" class="pagination-item">← Précédent</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($tools->getUrlRange(1, $tools->lastPage()) as $page => $url)
          @if ($page == $tools->currentPage())
            <span class="pagination-item active">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="pagination-item">{{ $page }}</a>
          @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($tools->hasMorePages())
          <a href="{{ $tools->nextPageUrl() }}" class="pagination-item">Suivant →</a>
        @else
          <span class="pagination-item disabled">Suivant →</span>
        @endif
      </div>
    @endif

  @else
    <!-- ─────────────────────────────────────────────────────────────
         EMPTY STATE
         ───────────────────────────────────────────────────────────── -->

    <div class="no-results">
      <div class="no-results-icon">🔍</div>
      <h3 class="no-results-title">
        @if(request('search'))
          Aucun outil trouvé
        @else
          Pas encore d'outils API
        @endif
      </h3>
      <p class="no-results-text">
        @if(request('search'))
          Essayez une autre recherche ou 
          <a href="{{ route('tools.index') }}" class="text-accent hover:underline">voir tous les outils</a>
        @else
          Créez votre premier outil API pour commencer
        @endif
      </p>
      @if(!request('search'))
        <a href="{{ route('tools.create') }}" class="btn btn-primary">
          ✚ Créer un Outil
        </a>
      @endif
    </div>
  @endif
</div>

@endsection

<!-- ─────────────────────────────────────────────────────────────────
     SCRIPTS
     ───────────────────────────────────────────────────────────────── -->

@push('scripts')
<script>
  function exportTools() {
    // Exemple d'export
    const tools = @json($tools);
    const csv = [
      ['Nom', 'Endpoint', 'Connexion', 'Status'].join(','),
      ...tools.map(t => [
        `"${t.name}"`,
        `"${t.endpoint}"`,
        `"${t.connection?.name || 'N/A'}"`,
        t.is_active ? 'Actif' : 'Inactif'
      ].join(','))
    ].join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'outils.csv';
    a.click();
  }
</script>
@endpush

<!-- ═══════════════════════════════════════════════════════════════════
     NOTES:
     
     1. Classes CSS utilisées:
        - page-header, page-title, page-actions
        - search-filter-bar, search-filter-container, search-group
        - filter-select, results-info
        - table-wrapper, table, table-striped
        - cell-status, status-active, status-inactive
        - table-actions, action-btn, delete
        - pagination, pagination-item, disabled
        - no-results, no-results-icon, no-results-title
        - btn, btn-primary, btn-secondary
        - gradient-text, highlight-text, text-accent, text-secondary
        - animate-fade-in-down, animate-slide-in-left, delay-100, animate-fade-in-up
     
     2. Plus besoin de:
        - Classes Tailwind (flex, gap, px-, py-, rounded-, etc.)
        - Styles inline
        - Couleurs hardcoded
     
     3. Responsive automatique (mobile/tablet)
     
     4. Dark/light mode automatique via CSS variables
     
     5. Animations fluides et performantes
     
     ═══════════════════════════════════════════════════════════════════ -->
