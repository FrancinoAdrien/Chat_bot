/**
 * Theme Switcher - Gère le basculement entre dark et light mode
 * Persiste la préférence de l'utilisateur en localStorage
 */

class ThemeSwitcher {
  constructor() {
    this.storageKey = 'app-theme-preference';
    this.lightModeIcon = '☀️';
    this.darkModeIcon = '🌙';
    this.init();
  }

  /**
   * Initialise le commutateur de thème
   */
  init() {
    this.loadTheme();
    this.setupEventListeners();
    this.syncSystemPreference();
  }

  /**
   * Charge le thème depuis le localStorage ou utilise la préférence système
   */
  loadTheme() {
    const savedTheme = localStorage.getItem(this.storageKey);
    
    if (savedTheme) {
      this.setTheme(savedTheme);
    } else {
      // Utilise la préférence système si disponible
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      this.setTheme(prefersDark ? 'dark' : 'light');
    }
  }

  /**
   * Change le thème
   * @param {string} theme - 'dark' ou 'light'
   */
  setTheme(theme) {
    const resolvedTheme = theme === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', resolvedTheme);
    localStorage.setItem(this.storageKey, resolvedTheme);
    this.updateThemeToggleButtons();
    window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: resolvedTheme } }));
  }

  /**
   * Alterne entre dark et light mode
   */
  toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    this.setTheme(newTheme);
  }

  /**
   * Obtient le thème actuel
   * @returns {string} - 'dark' ou 'light'
   */
  getCurrentTheme() {
    return document.documentElement.getAttribute('data-theme') || 'dark';
  }

  /**
   * Met à jour les boutons de basculement de thème
   */
  updateThemeToggleButtons() {
    const currentTheme = this.getCurrentTheme();
    const buttons = document.querySelectorAll('[data-toggle-theme]');

    buttons.forEach(btn => {
      const isActive = btn.getAttribute('data-toggle-theme') === currentTheme;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-pressed', isActive);
    });
  }

  /**
   * Configure les écouteurs d'événements
   */
  setupEventListeners() {
    // Boutons de basculement de thème
    document.addEventListener('click', (e) => {
      const themeBtn = e.target.closest('[data-toggle-theme]');
      if (themeBtn) {
        const theme = themeBtn.getAttribute('data-toggle-theme');
        this.setTheme(theme);
      }
    });

    // Écouteur pour les changements de préférence système
    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        const savedTheme = localStorage.getItem(this.storageKey);
        if (!savedTheme) {
          this.setTheme(e.matches ? 'dark' : 'light');
        }
      });
    }
  }

  /**
   * Synchronise avec la préférence système
   */
  syncSystemPreference() {
    const savedTheme = localStorage.getItem(this.storageKey);
    if (!savedTheme && window.matchMedia) {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      this.setTheme(prefersDark ? 'dark' : 'light');
    }
  }
}

// Initialiser le commutateur de thème
// S'assurer que ça marche peu importe quand le script est chargé
function initThemeSwitcher() {
  window.themeSwitcher = new ThemeSwitcher();
  console.log('✅ ThemeSwitcher initialized');
  console.log('Current theme:', window.themeSwitcher.getCurrentTheme());
}

// Si le DOM est déjà chargé
if (document.readyState !== 'loading') {
  initThemeSwitcher();
} else {
  // Si le DOM n'est pas encore chargé
  document.addEventListener('DOMContentLoaded', initThemeSwitcher);
}

// Fallback: initialiser après 100ms
setTimeout(() => {
  if (!window.themeSwitcher) {
    initThemeSwitcher();
  }
}, 100);

// Exporter pour utilisation en module
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ThemeSwitcher;
}
