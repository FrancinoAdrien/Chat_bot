# 📚 Documentation Complète du Projet ChatBot IA

> Une documentation détaillée pour comprendre l'architecture, les fonctionnalités et les dépendances du projet chatbot avec intégration IA.

---

## Table des Matières

1. [Vue d'ensemble du projet](#vue-densemble-du-projet)
2. [Architecture générale](#architecture-générale)
3. [Stack Technologique](#stack-technologique)
4. [Installation et Configuration](#installation-et-configuration)
5. [Structure des fichiers](#structure-des-fichiers)
6. [Fonctionnalités principales](#fonctionnalités-principales)
7. [Base de données](#base-de-données)
8. [API et Routes](#api-et-routes)
9. [Système des Outils (Tools)](#système-des-outils-tools)
10. [Services IA](#services-ia)
11. [Services API](#services-api)
12. [Authentification](#authentification)
13. [Gestion des Sessions](#gestion-des-sessions)
14. [Intelligence Artificielle (IA)](#intelligence-artificielle-ia)
15. [Modèles de Données](#modèles-de-données)
16. [Configuration](#configuration)
17. [Commandes Artisan](#commandes-artisan)
18. [Déploiement](#déploiement)

---

## Vue d'ensemble du projet

### Objectif Principal
Le projet est un **chatbot intelligent avec intégration IA** qui permet aux utilisateurs d'interagir avec un assistant capable de :
- Comprendre les intentions utilisateur
- Exécuter des outils/actions (appels API, requêtes Excel)
- Fournir des réponses intelligentes basées sur les données
- Maintenir un historique conversationnel
- Utiliser plusieurs fournisseurs IA (Groq, OpenAI, Gemini, Ollama local)

### Cas d'usage principaux
- **Chatbot métier** : Interroger des données de ventes, stocks, produits
- **Assistant IA** : Fournir des réponses intelligentes et contextuelles
- **Intégration API** : Connecter à plusieurs sources de données externes
- **Extraction de données** : Traiter des fichiers Excel pour les analyses

---

## Architecture générale

```
┌─────────────────────────────────────────────────┐
│           Frontend (Vite + Tailwind CSS)         │
├─────────────────────────────────────────────────┤
│    Web Routes (Chat UI) + API Routes (JSON)     │
├─────────────────────────────────────────────────┤
│           Laravel Application (v13.8)           │
│  ┌───────────────────────────────────────────┐  │
│  │  Controllers                              │  │
│  │  ├── ChatController (Web + API)           │  │
│  │  ├── ToolController (Gestion des outils) │  │
│  │  ├── AuthController (Authentification)    │  │
│  │  └── ...autres controllers                │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│           Services (Couche métier)              │
│  ┌───────────────────────────────────────────┐  │
│  │ ChatService       - Orchestration chat    │  │
│  │ GroqService       - Appels API Groq       │  │
│  │ OllamaService     - Modèle local Ollama   │  │
│  │ DynamicApiService - Appels API dynamiques │  │
│  │ RemoteApiService  - Gestion API distantes │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│           Module IA (Traitement)                │
│  ┌───────────────────────────────────────────┐  │
│  │ IntentDetector  - Détection d'intention   │  │
│  │ ToolManager     - Exécution d'outils      │  │
│  │ PromptBuilder   - Construction de prompts │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│           Modèles (ORM Eloquent)                │
│  ├── ChatSession                                │
│  ├── Conversation                               │
│  ├── Tool                                       │
│  ├── ApiConnection                              │
│  ├── AiProviderSetting                          │
│  ├── AiRule                                     │
│  ├── User                                       │
│  └── ...autres modèles                          │
├─────────────────────────────────────────────────┤
│           Base de Données                       │
│  ├── MySQL/SQLite                               │
│  └── Migrations + Seeders                       │
├─────────────────────────────────────────────────┤
│       Fournisseurs IA Externes                  │
│  ├── Groq (Llama 3, gratuit & rapide)          │
│  ├── OpenAI (GPT-4o)                           │
│  ├── Google Gemini                             │
│  └── Ollama (local)                            │
└─────────────────────────────────────────────────┘
```

---

## Stack Technologique

### Backend
| Technologie | Version | Rôle |
|---|---|---|
| **Laravel** | ^13.8 | Framework PHP principal |
| **PHP** | ^8.3 | Langage de programmation |
| **MySQL/SQLite** | - | Base de données |
| **PHPUnit** | ^12.5.12 | Testing |

### Frontend
| Technologie | Version | Rôle |
|---|---|---|
| **Vite** | ^8.0.0 | Build tool & dev server |
| **Tailwind CSS** | ^4.0.0 | Framework CSS utilitaire |
| **@tailwindcss/vite** | ^4.0.0 | Plugin Vite pour Tailwind |

### Services IA / Outils
| Service | Rôle |
|---|---|
| **Groq API** | Fournisseur IA cloud (Llama 3) |
| **OpenAI** | GPT-4o (optionnel) |
| **Google Gemini** | Gemini 1.5 (optionnel) |
| **Ollama** | Modèles LLM locaux (Llama, Mistral, etc.) |

### Dépendances importantes
```php
// Production
- laravel/framework: ^13.8
- laravel/tinker: ^3.0
- phpoffice/phpspreadsheet: ^5.8 (Traitement Excel)

// Développement
- laravel/boost: ^2.2 (Outils AI)
- laravel/pint: ^1.27 (Linting)
- phpunit/phpunit: ^12.5.12 (Tests)
- mockery/mockery: ^1.6 (Mocking)
```

---

## Installation et Configuration

### Prérequis
- PHP 8.3+
- Composer
- Node.js 18+
- npm ou yarn
- Laravel 13.8

### Installation complète

```bash
# 1. Cloner le projet
git clone <repo-url>
cd chatBot

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier .env
cp .env.example .env

# 4. Générer la clé Laravel
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=chatbot
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Exécuter les migrations
php artisan migrate

# Creation admin
php artisan user:create-admin

# 7. (Optionnel) Seeder d'admin
php artisan db:seed

# 8. Installer les dépendances Node.js
npm install

# 9. Compiler les assets
npm run build
# ou pour le développement
npm run dev
```

### Commande Setup Automatique
```bash
composer run-script setup
```

Cette commande exécute automatiquement :
1. `composer install` - Installe PHP packages
2. Génère `.env` s'il n'existe pas
3. `php artisan key:generate` - Génère la clé app
4. `php artisan migrate --force` - Exécute migrations
5. `npm install --ignore-scripts` - Installe JS packages
6. `npm run build` - Compile les assets

---

## Structure des fichiers

### Arborescence complète

```
chatBot/
├── app/
│   ├── AI/                          # Module Intelligence Artificielle
│   │   ├── IntentDetector.php       # Détecte l'intention utilisateur
│   │   ├── PromptBuilder.php        # Construit les prompts pour l'IA
│   │   └── ToolManager.php          # Gère l'exécution des outils
│   ├── DTOs/                        # Data Transfer Objects
│   │   └── ChatResponseDTO.php      # DTO pour réponse chat
│   ├── Http/
│   │   ├── Controllers/             # Contrôleurs MVC
│   │   │   ├── ChatController.php   # Chat (Web + API)
│   │   │   ├── ToolController.php   # Gestion des outils
│   │   │   ├── AuthController.php   # Authentification
│   │   │   ├── AiRuleController.php # Règles IA
│   │   │   ├── AiProviderController.php # Fournisseurs IA
│   │   │   ├── ApiConnectionController.php # Connexions API
│   │   │   └── ...autres
│   │   ├── Middleware/              # Middlewares
│   │   └── Requests/                # Form Requests (validation)
│   ├── Models/                      # Modèles Eloquent ORM
│   │   ├── ChatSession.php          # Session de chat
│   │   ├── Conversation.php         # Message dans une session
│   │   ├── Tool.php                 # Définition des outils
│   │   ├── ApiConnection.php        # Connexion API externe
│   │   ├── AiProviderSetting.php    # Configuration fournisseur IA
│   │   ├── AiRule.php               # Règles/directives pour l'IA
│   │   ├── ToolRelation.php         # Relations entre outils (ERD)
│   │   ├── User.php                 # Utilisateurs
│   │   └── ...autres
│   ├── Providers/
│   │   ├── AppServiceProvider.php   # Service provider principal
│   │   └── ...autres providers
│   ├── Services/                    # Couche métier
│   │   ├── ChatService.php          # Orchestration principale du chat
│   │   ├── GroqService.php          # Appels API Groq (cloud IA)
│   │   ├── OllamaService.php        # Appels Ollama (local LLM)
│   │   ├── DynamicApiService.php    # Appels API dynamiques
│   │   └── RemoteApiService.php     # Gestion API distantes
│   └── Tools/                       # Outils exécutables
│       ├── BaseTool.php             # Classe de base pour les outils
│       ├── GetSalesTodayTool.php    # Outil: Ventes du jour
│       ├── GetMonthlySalesTool.php  # Outil: Ventes du mois
│       ├── GetTopProductsTool.php   # Outil: Top produits
│       └── GetLowStockTool.php      # Outil: Stock faible
│
├── bootstrap/
│   ├── app.php                      # Initialisation app Laravel
│   └── providers.php                # Enregistrement des providers
│
├── config/                          # Fichiers de configuration
│   ├── app.php                      # Config applicatif
│   ├── database.php                 # Config base de données
│   ├── mail.php                     # Config emails
│   ├── cache.php                    # Config cache
│   ├── session.php                  # Config sessions
│   ├── services.php                 # Config services
│   ├── auth.php                     # Config authentification
│   ├── logging.php                  # Config logging
│   ├── queue.php                    # Config queues
│   ├── filesystems.php              # Config disques
│   └── ollama.php                   # Config Ollama (LLM local)
│
├── database/
│   ├── migrations/                  # Migrations SQL
│   │   ├── *_create_users_table.php
│   │   ├── *_create_conversations_table.php
│   │   ├── *_create_tools_table.php
│   │   ├── *_create_api_connections_table.php
│   │   ├── *_create_ai_provider_settings_table.php
│   │   └── ...autres migrations
│   ├── factories/                   # Factories de test
│   │   └── UserFactory.php
│   └── seeders/                     # Seeders de données
│       └── AdminUserSeeder.php      # Crée utilisateur admin
│
├── public/                          # Dossier public (web root)
│   ├── index.php                    # Point d'entrée
│   ├── robots.txt
│   ├── storage/                     # Fichiers publics
│   └── build/                       # Assets compilés (Vite)
│
├── resources/
│   ├── css/                         # Feuilles de style CSS
│   ├── js/                          # Code JavaScript
│   └── views/                       # Templates Blade
│
├── routes/
│   ├── api.php                      # Routes API REST
│   ├── web.php                      # Routes Web (MVC)
│   └── console.php                  # Commandes Artisan
│
├── storage/                         # Stockage runtime
│   ├── app/                         # Fichiers utilisateurs
│   ├── framework/                   # Cache, sessions, views compilées
│   └── logs/                        # Fichiers logs
│
├── tests/                           # Tests PHPUnit
│   ├── Feature/                     # Tests fonctionnels
│   └── Unit/                        # Tests unitaires
│
├── vendor/                          # Packages Composer (gitignored)
│
├── .env.example                     # Template de configuration
├── composer.json                    # Dépendances PHP
├── package.json                     # Dépendances Node.js
├── vite.config.js                   # Configuration Vite
├── phpunit.xml                      # Configuration PHPUnit
├── artisan                          # CLI Laravel
└── README.md                        # README standard Laravel
```

---

## Fonctionnalités principales

### 1. **Système de Chat avec IA**
- **Interface conversationnelle** : Chat en temps réel avec assistant IA
- **Multi-sessions** : Chaque utilisateur peut avoir plusieurs conversations
- **Historique complet** : Toutes les conversations sont sauvegardées
- **Recherche RAG** : Recherche par similarité dans l'historique

**Fichiers concernés :**
- `app/Http/Controllers/ChatController.php` - Gestion du chat
- `app/Services/ChatService.php` - Orchestration
- `resources/views/chat/` - Templates chat

### 2. **Détection d'Intention Intelligente**
- **Reconnaissance de keywords** : Détecte automatiquement les intentions utilisateur
- **Outils natifs** : "ventes aujourd'hui", "rupture stock", etc.
- **Outils dynamiques** : Outils personnalisés par API
- **Fallback intelligent** : Réutilise le dernier outil utilisé en contexte

**Fichiers concernés :**
- `app/AI/IntentDetector.php` - Logique de détection
- `app/Models/Tool.php` - Définition des outils

### 3. **Exécution d'Outils Dynamiques**
- **Outils natifs** : Ventes, stocks, produits top
- **Outils API** : Appels à APIs externes configurables
- **Outils Excel** : Traitement de fichiers spreadsheet
- **Exécution avec données** : Extraction de données en temps réel

**Fichiers concernés :**
- `app/Tools/BaseTool.php` - Classe parente
- `app/Tools/*.php` - Implémentations concrètes
- `app/AI/ToolManager.php` - Exécution

### 4. **Intégration Multiple de Fournisseurs IA**
- **Groq** : IA cloud rapide et gratuite (Llama 3)
- **OpenAI** : GPT-4o (payant)
- **Google Gemini** : Gemini 1.5 (optionnel)
- **Ollama** : Modèles locaux (gratuit, offline)

**Fichiers concernés :**
- `app/Services/GroqService.php` - Groq API
- `app/Services/OllamaService.php` - Ollama local
- `app/Models/AiProviderSetting.php` - Configuration des providers

### 5. **Gestion des Connexions API**
- **Authentification dynamique** : Support Bearer tokens, auth custom
- **URLs configurables** : Base URL + endpoints dynamiques
- **Gestion d'erreurs** : 401/403 handling, messages clairs
- **Tokens masqués** : Sécurité des données sensibles

**Fichiers concernés :**
- `app/Models/ApiConnection.php` - Modèle connexion
- `app/Services/DynamicApiService.php` - Appels dynamiques
- `app/Services/RemoteApiService.php` - Gestion distante

### 6. **Système de Règles IA**
- **Directives personnalisées** : Instructions pour guider l'IA
- **Règles par utilisateur** : Règles spécifiques à chaque utilisateur
- **Contexte applicatif** : Informations métier pour l'IA

**Fichiers concernés :**
- `app/Models/AiRule.php` - Modèle règles
- `app/Http/Controllers/AiRuleController.php` - Gestion

### 7. **Authentification et Autorisations**
- **Login/Logout** : Authentification Laravel standard
- **Rôles** : Admin vs utilisateurs normaux
- **Protections** : Routes protégées par middleware auth
- **Rate limiting** : Throttling sur les endpoints API

**Fichiers concernés :**
- `app/Http/Controllers/AuthController.php`
- `app/Http/Middleware/` - Middlewares personnalisés
- `routes/web.php` - Protection routes

### 8. **Admin Dashboard**
- **Gestion des outils** : CRUD pour les outils
- **Gestion des connexions API** : Configuration APIs
- **Configuration des fournisseurs IA** : Setup Groq, OpenAI, etc.
- **Définition des règles IA** : Directives personnalisées
- **Test d'outils** : Interface de test

**Fichiers concernés :**
- `app/Http/Controllers/ToolController.php`
- `app/Http/Controllers/ApiConnectionController.php`
- `app/Http/Controllers/AiProviderController.php`
- `app/Http/Controllers/AiRuleController.php`

---

## Base de Données

### Schéma principal

```
┌──────────────────────────┐
│ users                    │
├──────────────────────────┤
│ id (PK)                  │
│ name                     │
│ email (unique)           │
│ password                 │
│ role (admin|user)        │
│ created_at / updated_at  │
└──────────────┬───────────┘
               │
               │ 1:N
               ▼
┌──────────────────────────┐      ┌──────────────────────────┐
│ chat_sessions            │      │ api_connections          │
├──────────────────────────┤      ├──────────────────────────┤
│ id (PK)                  │──────│ id (PK)                  │
│ user_id (FK)             │      │ name                     │
│ api_connection_id (FK)   │      │ base_url                 │
│ title                    │      │ login_url                │
│ last_message_at          │      │ auth_token (encrypted)   │
│ created_at/updated_at    │      │ is_authenticated         │
└──────────┬───────────────┘      │ active                   │
           │                      │ created_at/updated_at    │
           │ 1:N                  └──────────────┬───────────┘
           ▼                                     │
┌──────────────────────────┐                    │
│ conversations            │                    │
├──────────────────────────┤                    │ 1:N
│ id (PK)                  │                    │
│ chat_session_id (FK)     │                    │
│ user_message             │                    ▼
│ ai_response              │      ┌──────────────────────────┐
│ tool_used                │      │ tools                    │
│ tool_data (JSON)         │      ├──────────────────────────┤
│ user_id (FK)             │      │ id (PK)                  │
│ created_at/updated_at    │      │ api_connection_id (FK)   │
└──────────────────────────┘      │ type (api|excel|native)  │
                                  │ name                     │
                                  │ label                    │
                                  │ endpoint                 │
                                  │ description              │
                                  │ keywords (JSON array)    │
                                  │ method (GET|POST|etc)    │
                                  │ active                   │
                                  │ file_path / sheet_name   │
                                  │ created_at/updated_at    │
                                  └──────────┬───────────────┘
                                             │
                                             │ M:N
                                             ▼
                                  ┌──────────────────────────┐
                                  │ tool_relations           │
                                  ├──────────────────────────┤
                                  │ id (PK)                  │
                                  │ primary_tool_id (FK)     │
                                  │ foreign_tool_id (FK)     │
                                  │ field_match (JSON)       │
                                  │ created_at/updated_at    │
                                  └──────────────────────────┘

┌──────────────────────────┐
│ ai_provider_settings     │
├──────────────────────────┤
│ id (PK)                  │
│ provider                 │
│ api_key (encrypted)      │
│ model                    │
│ is_active                │
│ verified_at              │
│ created_at/updated_at    │
└──────────────────────────┘

┌──────────────────────────┐
│ ai_rules                 │
├──────────────────────────┤
│ id (PK)                  │
│ user_id (FK)             │
│ rule_text                │
│ active                   │
│ created_at/updated_at    │
└──────────────────────────┘
```

### Migrations principales

| Migration | Rôle |
|---|---|
| `create_users_table` | Table utilisateurs |
| `create_cache_table` | Cache database |
| `create_jobs_table` | Queue jobs |
| `create_api_connections_table` | Connexions API |
| `create_conversations_table` | Messages chat |
| `create_tools_table` | Définition outils |
| `create_ai_provider_settings_table` | Config IA |
| `add_auth_fields_to_users_table` | Champs auth |
| Et autres... | |

---

## API et Routes

### Routes Web (Blade Templates)

```php
GET  /login                      // Page login
POST /login                      // Submit login
POST /logout                     // Logout

// Protected by 'auth' middleware
GET  /                           // Redirige vers chat
GET  /chat                       // Interface chat
POST /chat/send                  // Envoyer message (Web)
DELETE /chat/sessions/{session}  // Détruire session

// Admin only (middleware 'admin')
GET/POST/PUT/DELETE /tools                 // CRUD outils
GET /tools/{tool}/test                     // Page test outil
GET  /tool-relations                       // ERD explorer
GET  /tool-relations/schema/{tool}         // Schéma détaillé
POST /tool-relations                       // Créer relation
DELETE /tool-relations/{relation}          // Supprimer relation
GET/POST/PUT/DELETE /ai-rules              // CRUD règles IA
GET/POST/PUT/DELETE /providers             // CRUD fournisseurs IA
GET/POST/PUT/DELETE /api-connections      // CRUD connexions API
```

### Routes API REST (JSON)

```php
// API v1
prefix: /api/v1

POST /chat                      // Envoyer message (API)
  - Throttled: 60 requêtes/minute
  - Headers: X-ChatBot
  - Body: connection_id, message, session_id

GET /chat/history               // Historique chat
  - Headers: X-ChatBot

DELETE /chat/{conversation}     // Supprimer conversation
  - Headers: X-ChatBot
```

### Payloads API

#### Request Chat
```json
{
  "connection_id": 1,
  "message": "Quelles sont les ventes du jour?",
  "session_id": null
}
```

#### Response Chat
```json
{
  "success": true,
  "conversation": {
    "id": 1,
    "user_message": "Quelles sont les ventes du jour?",
    "ai_response": "Selon les données...",
    "tool_used": "getSalesToday",
    "tool_data": { "total": 15000, "items": [...] },
    "created_at": "2026-06-07T10:30:00Z"
  },
  "session": {
    "id": 1,
    "title": "Quelles sont les ventes du jour?",
    "last_message_at": "2026-06-07T10:30:00Z"
  }
}
```

---

## Système des Outils (Tools)

### Concept
Les **outils** sont des actions que le chatbot peut exécuter automatiquement en détectant l'intention utilisateur dans son message.

### Types d'outils

#### 1. **Outils Natifs** (intégrés)
```php
// Dans IntentDetector::$nativeTools
'getSalesToday'   => ['ventes aujourd\'hui', 'chiffre d\'affaires', 'sales today']
'getMonthlySales' => ['ventes du mois', 'mois en cours', 'monthly sales']
'getTopProducts'  => ['top produits', 'meilleurs produits', 'best sellers']
'getLowStock'     => ['rupture', 'faible stock', 'low stock']
```

**Emplacement :** `app/Tools/`
- `GetSalesTodayTool.php` - Récupère les ventes du jour
- `GetMonthlySalesTool.php` - Récupère les ventes du mois
- `GetTopProductsTool.php` - Liste les meilleurs produits
- `GetLowStockTool.php` - Produits en rupture de stock

#### 2. **Outils Dynamiques (API)**
Outils créés via l'admin pour appeler des APIs externes.

```php
// Exemple de Tool dynamique
Tool::create([
    'api_connection_id' => 1,
    'type'              => 'api',
    'name'              => 'getCustomers',
    'label'             => 'Get Customers',
    'endpoint'          => '/api/customers',
    'method'            => 'GET',
    'keywords'          => ['clients', 'customers', 'liste clients'],
    'description'       => 'Récupère la liste des clients',
    'active'            => true,
]);
```

#### 3. **Outils Excel**
Outils pour traiter des fichiers spreadsheet.

```php
Tool::create([
    'type'       => 'excel',
    'name'       => 'analyzeData',
    'file_path'  => 'storage/data.xlsx',
    'sheet_name' => 'Sales',
    'keywords'   => ['analyse', 'données'],
    'active'     => true,
]);
```

### Architecture d'un Outil

#### BaseTool (Classe parent)
```php
abstract class BaseTool
{
    // Endpoint relatif
    abstract public function endpoint(): string;
    
    // Méthode HTTP (GET, POST, etc.)
    public function method(): string { return 'GET'; }
    
    // Exécution avec client
    public function execute(Client $client): array { }
}
```

#### Exemple d'implémentation
```php
class GetSalesTodayTool extends BaseTool
{
    public function endpoint(): string
    {
        return '/api/sales/today';
    }
    
    public function method(): string
    {
        return 'GET';
    }
}
```

### Flux d'exécution

```
1. IntentDetector::detect()
   ├─ Cherche les keywords dans le message
   └─ Retourne nom d'outil (string)

2. ToolManager::execute()
   ├─ Récupère l'outil (Tool model)
   ├─ Instancie la classe d'outil
   └─ Appelle Tool->execute()

3. DynamicApiService/RemoteApiService
   ├─ Construit l'URL complète
   ├─ Ajoute le Bearer token
   ├─ Envoie la requête HTTP
   └─ Retourne les données

4. PromptBuilder::buildWithData()
   ├─ Formate les données pour le prompt
   ├─ Ajoute le contexte
   └─ Envoie à l'IA
```

### Gestion des Relations d'Outils (ERD)
Les **ToolRelation** permettent de définir des relations entre outils.

```php
// Exemple: relation entre Client et Order
ToolRelation::create([
    'primary_tool_id'   => $clientTool->id,
    'foreign_tool_id'   => $orderTool->id,
    'field_match'       => [
        'client.id' => 'order.client_id'
    ]
]);
```

**Utilité :** 
- Naviguer entre données liées
- Enrichir les réponses avec données associées
- Automatiser les requêtes en cascade

---

## Services IA

### 1. **GroqService** - API Cloud

**Configuration requise :**
- Clé API Groq : `AiProviderSetting` avec `provider='groq'`
- Modèle par défaut : `llama-3.3-70b-versatile`

**Utilisation :**
```php
$groqService = app(GroqService::class);
$response = $groqService->generate(
    prompt: $prompt,
    apiKey: $apiKey,
    model: 'llama-3.3-70b-versatile'
);
```

**Avantages :**
- ✅ Gratuit
- ✅ Rapide
- ✅ Puissant (Llama 3.3 70B)
- ✅ API complète

**Limitations :**
- ❌ Nécessite clé API et internet
- ❌ Rate limiting

**Fichier :** `app/Services/GroqService.php`

### 2. **OllamaService** - Modèles Locaux

**Configuration requise :**
- Ollama installé localement
- Configuration : `.env` + `config/ollama.php`
- Modèle téléchargé : `ollama pull llama3`

**Utilisation :**
```php
$ollamaService = app(OllamaService::class);
$response = $ollamaService->generate(
    prompt: $prompt,
    model: 'llama3'
);
```

**Avantages :**
- ✅ Entièrement local (offline)
- ✅ Aucun coût
- ✅ Données privées
- ✅ Pas de rate limiting

**Limitations :**
- ❌ Plus lent
- ❌ Moins performant que cloud
- ❌ Nécessite ressources PC

**Modèles disponibles :**
```php
'llama3'       => 'Llama 3 (8B)',
'llama3:70b'   => 'Llama 3 (70B)',
'llama3.1'     => 'Llama 3.1',
'mistral'      => 'Mistral 7B',
'gemma2'       => 'Gemma 2',
'phi3'         => 'Phi-3',
```

**Configuration :** `config/ollama.php`
```php
'url'     => env('OLLAMA_URL', 'http://localhost:11434'),
'model'   => env('OLLAMA_MODEL', 'llama3'),
'timeout' => (int) env('OLLAMA_TIMEOUT', 120),
'options' => [
    'temperature' => 0.7,
    'top_p'       => 0.9,
    'num_predict' => 1024,
],
```

**Fichier :** `app/Services/OllamaService.php`

### 3. **ChatService** - Orchestration

C'est le service principal qui orchestre tout.

**Flux :**
```php
ChatService::handle(
    $connection,      // API connection
    $userMessage,     // Message utilisateur
    $sessionId        // Session optionnelle
)
```

**Étapes d'exécution :**
1. Créer/récupérer ChatSession
2. Charger l'historique local
3. Charger l'historique global (RAG/Memory)
4. Récupérer les règles IA de l'utilisateur
5. Détecter l'intention → appeler ToolManager si besoin
6. Exécuter l'outil (si détecté)
7. Builder le prompt avec les données
8. Choisir le fournisseur IA (Groq cloud ou Ollama local)
9. Générer la réponse
10. Sauvegarder la conversation

**Fichier :** `app/Services/ChatService.php`

### Sélection du fournisseur IA

**Logique :**
```php
// 1. Cherche un fournisseur cloud actif
$cloudProvider = AiProviderSetting::activeCloud();

if ($cloudProvider) {
    // Utilise Groq (ou autre cloud)
    $aiResponse = $this->groqService->generate(...);
} else {
    // Fallback sur Ollama local
    $aiResponse = $this->ollamaService->generate(...);
}
```

**Fournisseurs cloud disponibles :**
- Groq (gratuit, recommandé)
- OpenAI (GPT-4o)
- Google Gemini

---

## Services API

### 1. **DynamicApiService** - Appels Génériques

Service pour appeler des APIs externes de manière générique.

**Utilisation :**
```php
$service = app(DynamicApiService::class);
$result = $service->call(
    connection: $apiConnection,
    endpoint: '/api/customers',
    method: 'GET',
    params: ['page' => 1]
);
```

**Gestion d'authentification :**
```php
// Ajoute Bearer token automatiquement si configuré
if ($connection->is_authenticated && $connection->auth_token) {
    $http = $http->withToken($connection->auth_token);
}
```

**Gestion d'erreurs :**
- `401/403` → Erreur auth (token expiré)
- `429` → Rate limit
- Timeouts → Erreur réseau
- Messages utilisateur-friendly

**Fichier :** `app/Services/DynamicApiService.php`

### 2. **RemoteApiService** - Gestion Distante

Service adapté pour les outils (legacy/alternatif).

**Utilisation :**
```php
$service = app(RemoteApiService::class);
$data = $service->call($client, $endpoint, $method);
```

**Fichier :** `app/Services/RemoteApiService.php`

---

## Authentification

### Système d'Authentification

Laravel utilise son système d'auth standard avec :
- **Guard :** `web` (default)
- **Provider :** `users` (Eloquent)
- **Token :** Session cookie
- **Hashage :** bcrypt

### Routes Authentification

```php
GET  /login        // Page login
POST /login        // Vérifier credentials
POST /logout       // Détruire session
```

### Contrôleur Auth

```php
AuthController::showLogin()    // Affiche formulaire
AuthController::login()        // Traite login
AuthController::logout()       // Logout
```

### Middleware d'Authentification

```php
// Protected routes
Route::middleware('auth')->group(function () {
    // Accès réservé utilisateurs connectés
});

// Admin only
Route::middleware('admin')->group(function () {
    // Accès réservé administrateurs
});
```

**Fichier :** `app/Http/Controllers/AuthController.php`

---

## Gestion des Sessions

### Modèle ChatSession

Représente une conversation (session de chat).

```php
class ChatSession extends Model
{
    // Attributs
    $user_id              // Utilisateur propriétaire
    $api_connection_id    // API utilisée
    $title                // Titre auto-généré
    $last_message_at      // Dernière interaction
    
    // Relations
    user()                // Utilisateur
    apiConnection()       // API connection
    conversations()       // Messages (Conversation model)
}
```

### Modèle Conversation

Représente un message dans une session.

```php
class Conversation extends Model
{
    // Attributs
    $chat_session_id      // Session parente
    $user_id              // Utilisateur
    $user_message         // Message de l'utilisateur
    $ai_response          // Réponse de l'IA
    $tool_used            // Outil utilisé (nullable)
    $tool_data            // Données de l'outil (JSON)
    $created_at           // Timestamp
    
    // Recherche
    Full-text search sur user_message + ai_response
}
```

### Flux de Session

```
1. Utilisateur envoie message
   └─> ChatService::handle($connection, $message, $sessionId)

2. Résoudre session
   ├─ Si $sessionId fourni → récupérer session
   └─ Sinon → créer nouvelle session

3. Charger historique local
   └─> $session->conversations()->get()

4. Charger RAG (historique global)
   └─> Conversations similaires d'autres sessions

5. Exécuter & sauvegarder
   └─> Créer nouvelle Conversation record

6. Retourner au frontend
   └─> JSON avec session + conversation
```

### Commandes de Gestion

```php
// Détruire une session
DELETE /chat/sessions/{session_id}

// Récupérer l'historique complet
GET /api/v1/chat/history

// Supprimer une conversation
DELETE /api/v1/chat/{conversation_id}
```

---

## Intelligence Artificielle (IA)

### 1. **IntentDetector** - Détection d'Intention

Analyse le message utilisateur pour déterminer quel outil utiliser.

**Algorithme :**
```
1. Normaliser message (minuscules, trim)
2. Chercher dans outils dynamiques (API)
3. Chercher dans outils Excel
4. Chercher dans outils natifs
5. Fallback: réutiliser dernier outil de la session
6. Retourner le nom de l'outil ou null
```

**Exemple :**
```php
$tool = $intentDetector->detect(
    message: "Quels sont les top produits?",
    connection: $connection,
    session: $chatSession
);
// Retourne: "getTopProducts"
```

**Fichier :** `app/AI/IntentDetector.php`

### 2. **ToolManager** - Exécution d'Outils

Trouve et exécute l'outil correspondant.

**Exemple :**
```php
$toolData = $toolManager->execute(
    toolName: "getTopProducts",
    connection: $connection
);
// Retourne: ['products' => [...], 'total' => 5]
```

**Flux :**
1. Récupérer Tool model par nom
2. Résoudre le type (natif, API, Excel)
3. Instancier la classe de l'outil
4. Appeler execute()
5. Retourner les données

**Fichier :** `app/AI/ToolManager.php`

### 3. **PromptBuilder** - Construction de Prompts

Construit des prompts sophistiqués pour l'IA.

**Contexte intégré :**
- Message utilisateur
- Outil utilisé + ses données
- Historique local (derniers N messages)
- Historique global (RAG)
- Règles IA de l'utilisateur
- Directives système

**Exemple de prompt :**
```
Vous êtes un assistant commercial intelligent.

[Directives système]
- Soyez concis et professionnel
- Répondez en français

[Historique]
Utilisateur: Quels sont nos clients?
Assistant: Voici nos clients...

[Données outils]
Outil utilisé: getTopProducts
Données:
{
  "products": ["Product A", "Product B"],
  "total_sales": 50000
}

[Règles utilisateur]
- Toujours inclure les montants en euros
- Format de réponse: tableau

[Message utilisateur]
Quels sont les meilleurs produits?

Répondez maintenant:
```

**Fichier :** `app/AI/PromptBuilder.php`

### Full-Text Search (RAG - Retrieval-Augmented Generation)

Le système utilise MySQL Full-Text Search pour retrouver les conversations similaires.

```php
// Dans ChatService::handle()
$globalHistory = Conversation::where('user_id', $userId)
    ->where('chat_session_id', '!=', $session->id)
    ->whereRaw("MATCH(user_message, ai_response) AGAINST(? IN NATURAL LANGUAGE MODE)", [$userMessage])
    ->limit(3)
    ->get();
```

**Utilité :**
- Enrichir le contexte avec des réponses similaires passées
- Améliorer la cohérence des réponses
- Personnaliser selon l'historique utilisateur

---

## Modèles de Données

### User
```php
// Utilisateurs
id, name, email, password, role (admin|user)

// Rôles
'admin'  → Accès complet dashboard
'user'   → Accès chat uniquement
```

### ChatSession
```php
// Session de chat
id, user_id, api_connection_id, title, last_message_at

// Titre auto-généré à partir du premier message
```

### Conversation
```php
// Message dans une session
id, chat_session_id, user_id
user_message, ai_response
tool_used, tool_data (JSON)
created_at

// Full-text searchable
```

### Tool
```php
// Définition d'un outil
id, api_connection_id, type (api|excel|native)
name, label, endpoint, description
keywords (JSON array), method, active
file_path, sheet_name (Excel)
```

### ApiConnection
```php
// Connexion API externe
id, name, base_url, login_url
auth_token (encrypted), is_authenticated
authenticated_at, description, active
```

### AiProviderSetting
```php
// Configuration fournisseur IA
id, provider (groq|openai|gemini|ollama)
api_key (encrypted), model, is_active, verified_at
```

### AiRule
```php
// Règles/Directives pour l'IA
id, user_id, rule_text, active
```

### ToolRelation
```php
// Relations entre outils
id, primary_tool_id, foreign_tool_id
field_match (JSON), created_at
```

---

## Configuration

### Variables d'Environnement (.env)

```bash
# Application
APP_NAME=ChatBot
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatbot
DB_USERNAME=root
DB_PASSWORD=

# Ollama (si local)
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3
OLLAMA_TIMEOUT=120

# Services externes
# (Groq, OpenAI, etc. - configurés via admin UI)

# Timeout API distantes
REMOTE_API_TIMEOUT=30

# Mail (optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465

# Queue (optionnel)
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=database

# Session
SESSION_DRIVER=cookie
```

### Fichiers Config

| Fichier | Rôle |
|---|---|
| `config/app.php` | Configuration app générale |
| `config/database.php` | Connexion DB |
| `config/auth.php` | Authentification |
| `config/cache.php` | Système de cache |
| `config/logging.php` | Logging (stdout, file, syslog) |
| `config/ollama.php` | Configuration Ollama local |
| `config/services.php` | Services externes |

### Configuration Admin UI

Disponible pour les administrateurs :

1. **AI Providers**
   - Ajouter clé API Groq/OpenAI/Gemini
   - Sélectionner modèle
   - Vérifier et activer

2. **API Connections**
   - Créer connexion API externe
   - URL base + endpoints
   - Authentification (Bearer token)
   - Tester la connexion

3. **Tools**
   - Créer outil API
   - Configurer endpoint + keywords
   - Définir type (api|excel|native)
   - Activer/désactiver

4. **AI Rules**
   - Ajouter directives système pour l'IA
   - Assigner par utilisateur
   - Exemple: "Réponds toujours en français"

5. **Tool Relations (ERD)**
   - Définir relations entre outils
   - Mapper les champs
   - Créer associations

---

## Commandes Artisan

### Commandes Utiles

```bash
# Setup complet
composer run-script setup

# Démarrage développement
composer run-script dev

# Migration
php artisan migrate              # Exécuter migrations
php artisan migrate:rollback     # Rollback
php artisan migrate:fresh        # Reset DB

# Seeding
php artisan db:seed              # Run all seeders
php artisan db:seed --class=AdminUserSeeder
php artisan user:create-admin    # Create admin

# Testing
composer run-script test
php artisan test

# Tinker (Shell interactif)
php artisan tinker

# Cache
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Queue (si utilisé)
php artisan queue:listen
php artisan queue:work

# Logs
php artisan logs:clear
```

### Commandes Boost (Laravel Boost)

```bash
php artisan boost:install    # Installation initiale
php artisan boost:update     # Update tools
```

---

## Déploiement

### Préparation Production

```bash
# 1. Vérifier l'environnement
php artisan env

# 2. Optimiser autoloader
composer install --optimize-autoloader --no-dev

# 3. Compiler config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Compiler assets
npm install --production
npm run build

# 5. Migration DB (si nécessaire)
php artisan migrate --force

# 6. Permissions
chmod -R 775 storage bootstrap/cache
```

### Configuration Serveur

#### Nginx
```nginx
server {
    listen 80;
    server_name chatbot.example.com;
    root /var/www/chatbot/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Apache
```apache
<Directory /var/www/chatbot/public>
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php/$1 [L]
    </IfModule>
</Directory>
```

### Monitoring & Maintenance

- **Logs** : `storage/logs/`
- **Performance** : Monitorer temps de réponse
- **DB** : Backups réguliers
- **Queue** : Surveiller les jobs
- **Security** : HTTPS, firewall

---

## Résumé des Fichiers Clés

| Fichier | Rôle |
|---|---|
| `app/Services/ChatService.php` | 🔴 CŒUR - Orchestration principale |
| `app/AI/IntentDetector.php` | 🟡 Détection intention |
| `app/AI/ToolManager.php` | 🟡 Exécution outils |
| `app/AI/PromptBuilder.php` | 🟡 Construction prompts |
| `app/Services/GroqService.php` | 🟢 IA cloud (Groq) |
| `app/Services/OllamaService.php` | 🟢 IA locale (Ollama) |
| `app/Services/DynamicApiService.php` | 🟢 Appels API externes |
| `app/Models/ChatSession.php` | 🔵 Session de chat |
| `app/Models/Conversation.php` | 🔵 Message conversation |
| `app/Models/Tool.php` | 🔵 Définition outil |
| `app/Models/ApiConnection.php` | 🔵 Connexion API |
| `app/Models/AiProviderSetting.php` | 🔵 Config IA |
| `app/Http/Controllers/ChatController.php` | 🟣 Contrôleur chat |
| `routes/api.php` | 🟣 Routes API |
| `routes/web.php` | 🟣 Routes Web |
| `config/ollama.php` | ⚙️ Config Ollama |

---

## Glossaire

| Terme | Définition |
|---|---|
| **Intention** | L'action que l'utilisateur demande (ex: "ventes du jour") |
| **Outil** | Action exécutable (API call, Excel read, etc.) |
| **ChatSession** | Conversation/session de chat d'un utilisateur |
| **Conversation** | Un message + réponse dans une session |
| **RAG** | Retrieval-Augmented Generation (historique comme contexte) |
| **Prompt** | Texte envoyé à l'IA pour générer réponse |
| **Provider IA** | Fournisseur de modèle (Groq, OpenAI, Ollama) |
| **Tool Relation** | Lien entre deux outils (Entity Relationship Diagram) |
| **Intent Detection** | Processus d'identification de l'intention utilisateur |
| **Full-Text Search** | Recherche textuelle complète (MySQL FTS) |

---

## Troubleshooting

### Problèmes courants

**Ollama ne répond pas**
```
Error: Connection refused on localhost:11434
Solution: Vérifier que Ollama est démarré: ollama serve
```

**Clé API Groq invalide**
```
Error: 401 Unauthorized
Solution: Vérifier clé API dans AiProviderSetting + app restarted
```

**Model Ollama non trouvé**
```
Error: 404 Model not found
Solution: Télécharger: ollama pull llama3
```

**Rate limiting API externe**
```
Error: 429 Too Many Requests
Solution: Augmenter délais entre requêtes ou ajouter rate limiting
```

**Base de données non migrée**
```
Error: Table 'conversations' doesn't exist
Solution: php artisan migrate
```

---

## Ressources & Liens Utiles

- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [Groq API Documentation](https://console.groq.com/docs)
- [Ollama GitHub](https://github.com/ollama/ollama)
- [OpenAI API](https://platform.openai.com/docs)
- [Google Gemini API](https://ai.google.dev/)

---

## Conclusion

Ce projet est un chatbot IA complet et flexible capable d'intégrer multiple fournisseurs IA, d'exécuter des outils personnalisés, et de maintenir un historique intelligent des conversations. Sa architecture modulaire permet une extension facile pour de nouveaux outils, providers IA, et fonctionnalités.

**Points forts :**
✅ Architecture modulaire et extensible
✅ Multiple fournisseurs IA
✅ Historique intelligent (RAG)
✅ Gestion d'outils sophistiquée
✅ Admin UI complète
✅ API REST scalable

**À améliorer :**
⚠️ Implémentation OpenAI/Gemini (actuellement Groq seulement)
⚠️ Streaming des réponses
⚠️ WebSocket pour temps réel
⚠️ Tests unitaires plus complets
⚠️ Documentation frontend (Vue/React)
⚠️ Analytics & monitoring

---

**Dernière mise à jour:** 7 Juin 2026  
**Version:** 1.0.0  
**Auteur:** Documentation Projet ChatBot

