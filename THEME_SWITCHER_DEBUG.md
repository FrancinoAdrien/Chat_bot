# 🔧 Guide de Débogage - Theme Switcher

## Problèmes Corrigés ✅

1. **Path CSS incorrect** - Changé `{{ asset('resources/css/main.css') }}` → `@vite()` avec chemins relatifs
2. **Imports CSS** - Changé `@import url()` → `@import './path'` pour Vite
3. **Variable CSS inexistante** - Corrigé `rgba(var(--color-bg-glass-rgb))` → `rgba(15, 23, 42, 0.8)`
4. **Script path incorrect** - Ajouté via `@vite()` au lieu de `{{ asset() }}`

## ✅ Comment Tester

### 1. Test HTML Autonome (Plus Facile)
```bash
# Ouvrir dans le navigateur
http://localhost:8000/test-theme.html

# Cliquer sur les boutons et observer:
# - Background change
# - Texte change
# - Boutons deviennent "active"
# - localStorage sauvegarde la préférence
```

### 2. Test dans Laravel App
```bash
# Aller sur une page admin
http://localhost:8000/tools

# Vérifier dans le navigateur:
# - Boutons 🌙/☀️ en haut à droite
# - Cliquer et observer les changements
# - Rafraîchir: le thème doit persister
```

## 🔍 Checklist de Débogage

### Console du Navigateur (F12 → Console)
```javascript
// Vérifier l'initialisation
window.themeSwitcher
// ✅ Devrait afficher: ThemeSwitcher { storageKey: "app-theme-preference", ... }

// Vérifier le thème actuel
window.themeSwitcher.getCurrentTheme()
// ✅ Devrait afficher: "dark" ou "light"

// Vérifier localStorage
localStorage.getItem('app-theme-preference')
// ✅ Devrait afficher: "dark" ou "light"

// Tester les changements
window.themeSwitcher.setTheme('light')
// Attendre 300ms... le background doit devenir blanc

window.themeSwitcher.setTheme('dark')
// Le background doit redevenir bleu foncé
```

### DevTools - Inspecteur HTML
```html
<!-- Vérifier l'attribut data-theme -->
<html data-theme="light" ...>  <!-- ✅ Present en light mode -->
<html>                         <!-- ✅ Absent en dark mode -->

<!-- Vérifier les styles appliqués -->
# Clic droit → Inspecter
# Vérifier l'onglet "Styles"
# Chercher "--color-bg-primary" 
# ✅ Doit changer au toggle
```

### DevTools - Computed Styles
```
# Dans l'inspecteur:
1. Clic droit sur <html>
2. Inspecter
3. Onglet "Computed"
4. Chercher "--color-bg-primary"
5. ✅ Dark: #0f172a
6. ✅ Light: #ffffff
```

## 🐛 Problèmes Courants

### Problem: Boutons ne répondent pas au clic
**Solution:**
```javascript
// Vérifier dans console
document.querySelectorAll('[data-toggle-theme]')
// ✅ Devrait afficher: NodeList(2) [button, button]
// ❌ Si vide: les boutons n'existent pas dans le DOM
```

### Problem: Thème ne change pas
**Solution:**
```css
/* Vérifier que les variables CSS sont définies */
:root { --color-bg-primary: #0f172a; } ✅
[data-theme="light"] { --color-bg-primary: #ffffff; } ✅

/* Vérifier que body utilise la variable */
body { background-color: var(--color-bg-primary); } ✅
```

### Problem: localStorage ne sauvegarde pas
**Solution:**
```javascript
// Vérifier que localStorage est accessible
localStorage.setItem('test', 'value')
localStorage.getItem('test')
// ❌ Si erreur: navigateur en mode privé ou localStorage désactivé
```

### Problem: CSS ne se charge pas
**Solution:**
```
1. F12 → Network
2. Charger la page
3. Chercher "main.css"
4. ✅ Status 200 (OK)
5. ❌ Status 404 (Not Found) = fichier manquant
6. ❌ Status 304 (Not Modified) = OK (cache)

Ou vérifier:
<head>
  <style>...</style> ✅
  <link rel="stylesheet" ...> ✅
</head>
```

## 📝 Checklist Finale

- [ ] Test HTML (`/test-theme.html`) fonctionne
- [ ] Boutons 🌙/☀️ visibles en haut à droite
- [ ] Clic sur bouton change le thème
- [ ] Changement prend 300ms (animation smooth)
- [ ] Rafraîchir page: thème persiste
- [ ] Nettoyer localStorage: thème par défaut (dark)
- [ ] DevTools Console: pas d'erreurs
- [ ] DevTools Network: main.css = 200 OK
- [ ] All pages admin avoir les boutons

## 🚀 Commandes Utiles

```bash
# Vider le cache du navigateur
# Ctrl+Shift+Delete (Windows/Linux)
# Cmd+Shift+Delete (Mac)

# Forcer le rechargement
# Ctrl+F5 (Windows)
# Cmd+Shift+R (Mac)

# Développement: Vérifier les logs Vite
npm run dev
# Chercher: "main.css" dans les logs

# Build production
npm run build
# Vérifier que main.css est dans dist/
```

## 📞 Aide

Si ça ne marche toujours pas:

1. **Copier l'erreur console exacte** (F12 → Console)
2. **Screenshot du DOM** (F12 → Inspecteur → HTML de <html>)
3. **Vérifier Network tab** (F12 → Network → rechargement)
4. **Tester sur `/test-theme.html`** d'abord

---

**Date**: 2026-06-11
**Status**: ✅ Corrections appliquées - Test recommandé
