# E-Exam API

A Laravel 12 REST API backend for an online examination platform.  
Supports three roles — **Admin**, **Teacher** (Enseignant), **Student** (Étudiant) — with JWT-based authentication, QCM/essay question support, automated correction, and result export.

## Features

- **Multi‑role auth** — register, login, JWT refresh, profile
- **Group management** — organise students into classes
- **Test lifecycle** — create → launch (timer starts) → student submits → auto/manual correction → results
- **Question types** — QCM (single/multiple choice), short answer, essay / development
- **Student attempts** — per‑test timing, auto‑save, submission tracking
- **Auto‑correction** — QCM and short‑answer questions scored automatically; essay questions wait for teacher review
- **Announcements** — publish per‑group updates
- **Result export** — upload / download result files (PDF, CSV, etc.)
- **Admin console** — approve / block users, view all tests, manage results
- **CLI tool** — `db:anonymize` command to replace real user data with fakes (optional backup)

## Tech Stack

| Layer       | Technology                                      |
|-------------|-------------------------------------------------|
| Framework   | Laravel 12                                      |
| Language    | PHP ^8.2                                        |
| Database    | MySQL 8+ (or compatible)                        |
| Auth        | JWT (`php-open-source-saver/jwt-auth`)          |
| Testing     | PHPUnit 11                                      |
| Assets      | Vite + TailwindCSS v4 (minimal, not the main UI) |

> The front‑end companion (React + TypeScript) lives in a separate repository.

## Prerequisites

- PHP **^8.2**
- Composer **^2**
- MySQL **8+**
- Node.js + npm (for asset building)
- Extensions: `BCMath`, `Ctype`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `pdo_mysql`

## Installation

```bash
# 1. Clone the repository
git clone <repo-url> back-eExam
cd back-eExam

# 2. Install PHP dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env (see Configuration section below)
#    Then run:
php artisan jwt:secret      # generate JWT signing key

# 5. Run migrations and seed demo data
php artisan migrate
php artisan db:seed --class=SimulationSeeder

# 6. Build front-end assets (optional — only needed if you use the Blade views)
npm install && npm run build

# 7. Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

## Configuration

### `.env` key settings

| Variable         | Description                          | Example                        |
|------------------|--------------------------------------|--------------------------------|
| `APP_URL`        | Base URL for the API                 | `http://localhost:8000`        |
| `DB_CONNECTION`  | Database driver                      | `mysql`                        |
| `DB_HOST`        | Database host                        | `127.0.0.1`                    |
| `DB_PORT`        | Database port                        | `3306`                         |
| `DB_DATABASE`    | Database name                        | `eexam`                        |
| `DB_USERNAME`    | Database user                        | `root`                         |
| `DB_PASSWORD`    | Database password                    | *(your password)*              |
| `JWT_SECRET`     | JWT signing key (generated)          | *(run `php artisan jwt:secret`)* |
| `JWT_ALGO`       | JWT algorithm                        | `HS256`                        |
| `JWT_TTL`        | Access token lifetime (minutes)      | `60`                           |
| `JWT_REFRESH_TTL`| Refresh token lifetime (minutes)     | `20160` (14 days)              |

### Authentication guard

The default guard is `api` with the `jwt` driver.  
All protected routes use the `auth:api` middleware.

Role‑based access uses the custom `role` middleware:

```php
// Examples (from routes/api.php)
Route::middleware(['auth:api', 'role:admin'])->group(...);
Route::middleware(['auth:api', 'role:enseignant,admin'])->group(...);
```

## API Endpoints

### Auth (`/api/auth`)

| Method | URI                          | Auth     | Description              |
|--------|------------------------------|----------|--------------------------|
| POST   | `/api/auth/register`         | Public   | Register a new user      |
| POST   | `/api/auth/login`            | Public   | Login                    |
| GET    | `/api/auth/profile`          | Auth     | Current user profile     |
| POST   | `/api/auth/logout`           | Auth     | Logout (invalidate token)|
| POST   | `/api/auth/refresh`          | Auth     | Refresh JWT token        |
| GET    | `/api/auth/{user}`           | Auth     | Show a specific user     |

### Admin (`/api/admin`)

| Method | URI                                    | Auth       | Description              |
|--------|----------------------------------------|------------|--------------------------|
| GET    | `/api/admin/users`                     | Admin      | List all users           |
| GET    | `/api/admin/users/pending`             | Admin      | List pending approvals   |
| POST   | `/api/admin/users/approve/{id}`        | Admin      | Approve a user           |
| POST   | `/api/admin/users/block/{id}`          | Admin      | Block a user             |

### Groups (`/api/groupes`)

| Method | URI                      | Auth           | Description             |
|--------|--------------------------|----------------|-------------------------|
| GET    | `/api/groupes`           | Public         | List all groups         |
| GET    | `/api/groupes/{group}`   | Public         | Show a group            |
| POST   | `/api/groupes`           | Auth           | Create a group          |
| PUT    | `/api/groupes/{id}`      | Auth           | Update a group          |
| DELETE | `/api/groupes/{id}`      | Auth           | Delete a group          |

### Tests (`/api/tests`)

| Method | URI                                         | Auth       | Description                  |
|--------|---------------------------------------------|------------|------------------------------|
| GET    | `/api/tests/{test}`                         | Auth       | Show a test                  |
| GET    | `/api/tests/groupe/{id_groupe}`             | Auth       | Tests for a group            |
| GET    | `/api/tests/user/{id_utilisateur}`          | Auth       | Tests created by a user      |
| GET    | `/api/tests/all_corrected`                  | Auth       | All corrected tests          |
| GET    | `/api/tests/all_corrected/admin`            | Admin      | Corrected tests (admin view) |
| GET    | `/api/tests/results/{id_test}`              | Auth       | Results / stats for a test   |
| GET    | `/api/tests/need_correction/{id}`           | Auth       | Tests needing manual grading |
| POST   | `/api/tests`                                | Teacher+   | Create a test                |
| PUT    | `/api/tests/{test}`                         | Teacher+   | Update a test                |
| PUT    | `/api/tests/launch/{test}`                  | Teacher+   | Launch a test (starts timer) |
| PUT    | `/api/tests/finish/{test}`                  | Auth       | Finish a test                |
| DELETE | `/api/tests/{test}`                         | Teacher+   | Delete a test                |

> `Teacher+` = routes that require the `role:enseignant,admin` middleware.

### Questions (`/api/questions`)

| Method | URI                                        | Auth       | Description                    |
|--------|--------------------------------------------|------------|--------------------------------|
| GET    | `/api/questions/{question}`                | Auth       | Show a question                |
| GET    | `/api/questions/test/{id_test}`            | Auth       | All questions for a test       |
| GET    | `/api/questions/test/random/{id_test}`     | Auth       | Random subset for a test       |
| POST   | `/api/questions`                           | Teacher+   | Create a question              |
| PUT    | `/api/questions/{id}`                      | Teacher+   | Update a question              |
| DELETE | `/api/questions/{id}`                      | Teacher+   | Delete a question              |

### Options (`/api/options`)

| Method | URI                                      | Auth       | Description                |
|--------|------------------------------------------|------------|----------------------------|
| GET    | `/api/options/question/{id_question}`    | Auth       | Options for a QCM question |
| POST   | `/api/options`                           | Teacher+   | Create an option           |
| DELETE | `/api/options/{id}`                      | Teacher+   | Delete an option           |

### Tentatives (`/api/tentatives`)

| Method | URI                                        | Auth | Description                    |
|--------|--------------------------------------------|------|--------------------------------|
| POST   | `/api/tentatives`                          | Auth | Start a test attempt           |
| PUT    | `/api/tentatives/{id_tentative}`           | Auth | Update attempt (save progress) |
| GET    | `/api/tentatives/test/{id_test}`           | Auth | Tentatives for a test          |
| GET    | `/api/tentatives/responses/{id_test}`      | Auth | Responses for a test           |

### Responses (`/api/reponses`)

| Method | URI                                      | Auth | Description                    |
|--------|------------------------------------------|------|--------------------------------|
| POST   | `/api/reponses`                          | Auth | Submit a response              |
| PUT    | `/api/reponses/{id}/texte`               | Auth | Update response text           |
| PUT    | `/api/reponses/corriger/{id}`            | Auth | Manually grade a response      |
| GET    | `/api/reponses/non-corrigees`            | Auth | List uncorrected responses     |
| GET    | `/api/reponses/test/{id_test}`           | Auth | Responses for a test           |
| GET    | `/api/reponses/{id}`                     | Auth | Show a response                |

### Announcements (`/api/annonces`)

| Method | URI                                              | Auth | Description                           |
|--------|--------------------------------------------------|------|---------------------------------------|
| GET    | `/api/annonces/{annonce}`                        | Auth | Show an announcement                  |
| GET    | `/api/annonces/groupe/{id_groupe}`               | Auth | Announcements for a group             |
| GET    | `/api/annonces/groupe/{id_groupe}/dernieres`     | Auth | Latest announcements for a group      |
| GET    | `/api/annonces/utilisateur/{id_utilisateur}`     | Auth | Latest announcements for a user       |
| POST   | `/api/annonces`                                  | Auth | Create an announcement                |
| PUT    | `/api/annonces/{id}`                             | Auth | Update an announcement                |
| DELETE | `/api/annonces/{id}`                             | Auth | Delete an announcement                |

### Results (`/api/resultats`)

| Method | URI                                      | Auth     | Description                  |
|--------|------------------------------------------|----------|------------------------------|
| GET    | `/api/resultats`                         | Admin    | List all results             |
| GET    | `/api/resultats/download/{id}`           | Auth     | Download a result file       |
| GET    | `/api/resultats/groupe/{id_groupe}`      | Auth     | Results for a group          |
| POST   | `/api/resultats`                         | Teacher+ | Create / upload a result     |
| DELETE | `/api/resultats/{id}`                    | Admin    | Delete a result              |

## Database Schema

### Entity-relationship diagram

```
┌───────────┐       ┌──────────────┐
│  groupes  │──1──N─│ utilisateurs │
└───────────┘       └──────────────┘
     │                     │
     │                     │ N
     │ 1                   │
     ├───────N──────┐      │
     │              │      │
┌───────────┐  ┌──────┐  ┌───────────┐
│  annonces │  │tests │  │ tentatives│
└───────────┘  └──────┘  └───────────┘
                    │           │
                    │ 1         │ N
                    │           │
               ┌──────────┐  ┌──────────────────┐
               │ questions│  │ reponses_etudiants│
               └──────────┘  └──────────────────┘
                    │
                    │ 1
                    │
              ┌────────────┐
              │ options_qcm│
              └────────────┘

┌───────────┐
│ resultats │──N──1── groupes
└───────────┘
```

### Key tables

| Table              | Primary key         | Notes                                      |
|--------------------|---------------------|--------------------------------------------|
| `groupes`          | `id_groupe`         | Student groups / classes                   |
| `utilisateurs`     | `id_utilisateur`    | Users (all roles); JWTSubject              |
| `tests`            | `id_test`           | Status managed via `TestStatus` enum       |
| `questions`        | `id_question`       | Type managed via `QuestionType` enum       |
| `options_qcm`      | `id_option`         | QCM answer options (`est_correcte` flag)   |
| `tentatives`       | `id_tentative`      | Student test attempts with timing          |
| `reponses_etudiants`| `id_reponse`       | Per‑question responses; auto‑correctable   |
| `annonces`         | `id_annonce`        | Group announcements                        |
| `resultats`        | `id_resultat`       | Result file references                     |

## Project Structure

```
back-eExam/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── AnonymizeData.php      # CLI: db:anonymize
│   ├── Enums/
│   │   ├── UserRole.php               # admin, enseignant, etudiant
│   │   ├── TestStatus.php             # En attente, En cours, Terminé
│   │   └── QuestionType.php           # qcm, reponse courte, developpement
│   ├── Http/
│   │   ├── Controllers/API/
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   ├── TestController.php
│   │   │   ├── QuestionController.php
│   │   │   ├── OptionController.php
│   │   │   ├── TentativeController.php
│   │   │   ├── ReponseController.php
│   │   │   ├── AnnonceController.php
│   │   │   ├── ResultatController.php
│   │   │   └── GroupController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php     # role:admin, role:enseignant,admin
│   ├── Models/
│   │   ├── Utilisateur.php
│   │   ├── Group.php
│   │   ├── Test.php
│   │   ├── Question.php
│   │   ├── OptionQcm.php
│   │   ├── Tentative.php
│   │   ├── Reponse.php
│   │   ├── Annonce.php
│   │   └── Resultat.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                        # Middleware registration
├── config/
│   ├── app.php
│   ├── auth.php                       # JWT guard config
│   ├── jwt.php                        # JWT settings (TTL, algo, etc.)
│   └── ...
├── database/
│   ├── migrations/                    # 14 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── SimulationSeeder.php       # Demo data (group, teacher, student, test)
├── routes/
│   └── api.php                        # All API route definitions
└── tests/
    ├── TestCase.php
    ├── Unit/
    │   ├── ExampleTest.php
    │   └── EnumValuesTest.php
    └── Feature/
        └── ExampleTest.php
```

## Testing

```bash
# Run all tests
php artisan test

# Or via Composer script (includes config:clear)
composer run test
```

The test suite covers enum value consistency and model casts.  
Extend with feature tests for API endpoints.

## Development Commands

```bash
# Start dev server + queue + log watcher + Vite hot‑reload
composer run dev

# Full first-time setup (composer install, .env, key:generate, migrate, npm)
composer run setup
```

### CLI: Anonymize Data

```bash
# Replace real user names/emails with fake data
php artisan db:anonymize

# Backup original data to CSV first
php artisan db:anonymize --backup
```

### Simulation Seeder

The `SimulationSeeder` creates demo data:

| Entity     | Details                                      |
|------------|----------------------------------------------|
| Group      | "Classe Test" (id=13)                        |
| Teacher    | `ens0001@univ.fr` / password (id=10)         |
| Student    | `etu001@univ.fr` / password (id=11)          |
| Test       | "Test simulation ens0001" (id=1005, 5 QCMs) |

## License

This project is open‑sourced under the [MIT license](https://opensource.org/licenses/MIT).
