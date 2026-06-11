# Guide d'Utilisation - Système CSS Modulaire

## 📋 Structure CSS

```
resources/css/
├── main.css                    # Fichier principal (point d'entrée)
├── variables.css               # Variables CSS (thèmes dark/light)
├── global.css                  # Styles globaux et reset
├── animations.css              # Keyframes et animations
├── components/
│   ├── layout.css             # Sidebar, header, layout principal
│   ├── buttons.css            # Tous les types de boutons
│   ├── forms.css              # Formulaires, inputs, validation
│   ├── tables.css             # Tables et pagination
│   ├── cards-modals.css       # Cards et modals
│   └── ...
└── pages/
    ├── admin-pages.css        # Pages admin (search, filters)
    ├── erd.css                # ERD diagram
    └── ...
```

## 🎨 Système de Thème

### Variables CSS (Dark Mode par défaut)
```css
:root {
  --color-bg-primary: #0f172a;
  --color-accent-primary: #818cf8;
  /* ... et plus */
}

/* Light Mode */
[data-theme="light"] {
  --color-bg-primary: #ffffff;
  --color-accent-primary: #6366f1;
  /* ... */
}
```

### Basculer le thème en JavaScript
```javascript
// Changer en light mode
window.themeSwitcher.setTheme('light');

// Changer en dark mode
window.themeSwitcher.setTheme('dark');

// Basculer
window.themeSwitcher.toggleTheme();

// Obtenir le thème actuel
const current = window.themeSwitcher.getCurrentTheme();
```

## 🔘 Classes CSS Principales

### Boutons
```html
<!-- Primary -->
<button class="btn btn-primary">Enregistrer</button>

<!-- Secondary -->
<button class="btn btn-secondary">Annuler</button>

<!-- Ghost -->
<button class="btn btn-ghost">Lien</button>

<!-- Variants -->
<button class="btn btn-danger">Supprimer</button>
<button class="btn btn-success">Confirmer</button>

<!-- Sizes -->
<button class="btn btn-sm">Petit</button>
<button class="btn btn-lg">Grand</button>
<button class="btn btn-icon">🔧</button>
```

### Formulaires
```html
<div class="form-group">
  <label class="required">Email</label>
  <input type="email" placeholder="example@mail.com">
  <div class="form-help">Entrez votre email</div>
</div>

<!-- Validation -->
<div class="form-group has-error">
  <input type="text">
  <div class="error-message">❌ Champ invalide</div>
</div>

<!-- Checkboxes -->
<div class="checkbox-group">
  <label class="checkbox-inline">
    <input type="checkbox"> Option 1
  </label>
</div>
```

### Tables
```html
<div class="table-wrapper">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Nom</th>
        <th>Email</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>John Doe</td>
        <td>john@mail.com</td>
        <td><span class="cell-status status-active">✓ Actif</span></td>
      </tr>
    </tbody>
  </table>
</div>
```

### Cards
```html
<div class="card">
  <div class="card-header">
    <h3>Titre du Card</h3>
  </div>
  <div class="card-body">
    Contenu du card
  </div>
  <div class="card-footer">
    <button class="btn btn-primary">Action</button>
  </div>
</div>

<!-- Variantes -->
<div class="card card-glass">Glass effect</div>
<div class="card card-gradient">Gradient</div>
<div class="card card-elevated">Ombré</div>
```

### Modals
```html
<!-- Structure HTML -->
<div class="modal-backdrop" id="myModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h2 class="modal-title">Titre</h2>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      Contenu
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary">Annuler</button>
      <button class="btn btn-primary">Confirmer</button>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script>
function openModal() {
  document.getElementById('myModal').classList.add('active');
}
function closeModal() {
  document.getElementById('myModal').classList.remove('active');
}
</script>
```

## ✨ Animations & Transitions

### Classes d'animation
```html
<!-- Fade -->
<div class="animate-fade-in">Fade in</div>

<!-- Slide -->
<div class="animate-slide-in-left">Glisse de gauche</div>
<div class="animate-slide-in-right">Glisse de droite</div>

<!-- Scale -->
<div class="animate-scale-in">Apparition progressive</div>

<!-- Effets -->
<div class="animate-pulse">Pulse</div>
<div class="animate-float">Flottement</div>
<div class="animate-glow">Luminosité</div>

<!-- Delay -->
<div class="animate-fade-in delay-100">Avec délai 100ms</div>
<div class="animate-fade-in delay-300">Avec délai 300ms</div>
```

### Classes de transition
```html
<!-- Transitions rapides -->
<div class="transition-fast">Transition rapide (150ms)</div>
<div class="transition-base">Transition normale (300ms)</div>
<div class="transition-slow">Transition lente (500ms)</div>

<!-- Effets hover -->
<div class="hover-scale">S'agrandit au survol</div>
<div class="hover-lift">Soulève légèrement</div>
<div class="hover-glow">Lueur au survol</div>
```

## 🔍 Pages Admin - Recherche & Filtres

### Structure HTML
```html
<div class="page-header">
  <h1 class="page-title">
    <span class="gradient-text">Outils API</span>
  </h1>
  <div class="page-actions">
    <button class="btn btn-primary">+ Nouveau</button>
  </div>
</div>

<!-- Barre de recherche et filtres -->
<div class="search-filter-bar">
  <div class="search-filter-container cols-3">
    <!-- Recherche -->
    <div class="search-group">
      <input type="text" placeholder="Rechercher...">
      <span class="search-icon">🔍</span>
    </div>
    
    <!-- Filtre -->
    <select class="filter-select">
      <option>Tous</option>
      <option>Actif</option>
      <option>Inactif</option>
    </select>
  </div>
</div>

<!-- Résultats info -->
<div class="results-info">
  <span class="results-count">
    Affichage <strong>12</strong> de <strong>45</strong> résultats
  </span>
</div>

<!-- Table avec résultats -->
<div class="table-wrapper">
  <table class="table">
    <!-- ... -->
  </table>
</div>
```

## 🔗 ERD (Entity Relationship Diagram)

### Structure HTML
```html
<div class="erd-wrapper">
  <!-- Contrôles -->
  <div class="erd-header">
    <h2 class="erd-title">Modélisation ERD</h2>
    <div class="erd-controls">
      <div class="erd-controls-group">
        <button class="erd-control-btn" onclick="zoomIn()">+</button>
        <span class="zoom-level" id="zoomLevel">100%</span>
        <button class="erd-control-btn" onclick="zoomOut()">−</button>
        <button class="erd-control-btn" onclick="resetZoom()">↺</button>
      </div>
      
      <!-- Recherche -->
      <div class="erd-search">
        <input type="text" placeholder="Rechercher une table...">
        <span class="erd-search-icon">🔍</span>
      </div>
    </div>
  </div>

  <!-- Canvas -->
  <div class="erd-container">
    <div class="erd-stage" id="erdStage">
      <svg class="erd-connections" id="erdConnections"></svg>
      
      <!-- Entités (Tables) -->
      <div class="erd-entity" style="left: 50px; top: 50px;">
        <h3 class="erd-entity-name">Users</h3>
        <ul class="erd-fields">
          <li class="erd-field primary-key">
            <span class="erd-field-name">id</span>
            <span class="erd-field-type">INT</span>
          </li>
          <li class="erd-field">
            <span class="erd-field-name">name</span>
            <span class="erd-field-type">VARCHAR</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Statistiques -->
  <div class="erd-stats">
    <div class="stat-item">
      <div class="stat-icon">📊</div>
      <div>
        <div class="stat-label">Tables</div>
        <div class="stat-value">12</div>
      </div>
    </div>
  </div>
</div>
```

## 🎯 Mise à Jour des Vues Blade

### Exemple : Page Tools (avant)
```blade
<div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-800">
      <tr>
        <th class="px-6 py-3 text-left">Nom</th>
      </tr>
    </thead>
    <tbody>
      @foreach($tools as $tool)
      <tr class="border-b hover:bg-slate-800">
        <td class="px-6 py-3">{{ $tool->name }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
```

### Exemple : Page Tools (après)
```blade
<div class="table-wrapper">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Nom</th>
      </tr>
    </thead>
    <tbody>
      @foreach($tools as $tool)
      <tr>
        <td>{{ $tool->name }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
```

## 🚀 Utilisation dans les Blade Templates

### 1. Importer CSS dans layout.blade.php (DÉJÀ FAIT)
```blade
<!-- Custom CSS (Modular Components) -->
<link rel="stylesheet" href="{{ asset('resources/css/main.css') }}">
```

### 2. Utiliser les classes CSS
```blade
<!-- Bouton -->
<button class="btn btn-primary">Enregistrer</button>

<!-- Card -->
<div class="card">
  <div class="card-header">
    <h3>Titre</h3>
  </div>
  <div class="card-body">
    Contenu
  </div>
</div>

<!-- Formulaire -->
<div class="form-group">
  <label class="required">Nom</label>
  <input type="text" placeholder="Nom...">
</div>
```

## 📱 Responsivité

Toutes les classes CSS incluent des media queries pour mobile/tablet :

```css
@media (max-width: 768px) {
  /* Styles mobiles */
}

@media (max-width: 640px) {
  /* Styles petits écrans */
}
```

## ⚙️ Configuration

### Changer les couleurs (variables.css)
```css
:root {
  --color-accent-primary: #818cf8; /* Changer cette couleur */
  --color-bg-primary: #0f172a;
}
```

### Changer les espacements
```css
:root {
  --spacing-md: 1rem;  /* Changer cet espacement */
  --spacing-lg: 1.5rem;
}
```

### Changer les animations
```css
:root {
  --transition-base: 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

## 📚 Ressources

- **CSS Variables**: Modifiables en temps réel via JavaScript
- **Dark/Light Mode**: Basculer via `data-theme="light"` sur `<html>`
- **Animations**: Keyframes définies dans `animations.css`
- **Responsive**: Mobile-first approach

## ✅ Checklist de Migration

- [ ] Importer `main.css` dans layout.blade.php
- [ ] Ajouter theme switcher dans header
- [ ] Tester dark mode et light mode
- [ ] Mettre à jour pages admin avec classes CSS
- [ ] Tester sur mobile (responsive)
- [ ] Vérifier animations et transitions
- [ ] Personnaliser couleurs dans variables.css si nécessaire
