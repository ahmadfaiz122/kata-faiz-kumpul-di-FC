# Technology Stack — SkillSwapp

This document summarizes **all the technologies and frameworks** used in this repository. This repository consists of two separate applications (frontend and backend) that communicate via a REST API.

## 1. Backend — `backend/` (Laravel)

### 1.1 Language & Runtime
- **PHP** `^8.2` — the backend's core language.

### 1.2 Framework
- **Laravel Framework** `v12.68.0` (`laravel/framework: ^12.0`) — the main MVC framework; provides routing, Eloquent ORM, service container, queue, cache, session, validation, etc.

### 1.3 Authentication & Authorization
- **Laravel Sanctum** `v4.3.3` (`laravel/sanctum: ^4.0`) — token-based (Bearer token / personal access token) API authentication, used to protect all routes in `routes/api.php` (`middleware('auth:sanctum')`).
- **Laravel Socialite** `v5.31.0` (`laravel/socialite: *`) — third-party OAuth integration; used here specifically for **Google login**.
  - **firebase/php-jwt** `v7.1.0` — decodes JWTs from Google (a Socialite dependency).
  - **league/oauth1-client** `v1.11.0` — supporting dependency for Socialite's OAuth1 protocol.
  - **phpseclib/phpseclib** `v4.0.1` — cryptographic operations (RSA/SSL) used by Socialite/JWT.
- Email domain is restricted manually in `GoogleAuthController` (`$allowedDomains = ['mhs.unesa.ac.id']`) — this is application logic, not a library.

### 1.4 HTTP Client & Networking
- **guzzlehttp/guzzle** `7.15.5` — PHP HTTP client, used by Socialite to call Google's endpoints.
- **guzzlehttp/psr7**, **guzzlehttp/promises**, **guzzlehttp/uri-template** — supporting dependencies for Guzzle (PSR-7 & promise implementations).
- **fruitcake/php-cors** `v1.4.0` — handles CORS headers (configured via `config/cors.php`).

### 1.5 Database & ORM
- **Eloquent ORM** (part of `illuminate/database`, bundled with the `laravel/framework` package) — the main ORM for the `User`, `Profile`, `Skill`, `Achievement`, `Post`, `PostTimeline`, `SkillRequest`, and `Transaction` models.
- **Configured database drivers** (`config/database.php`):
  - **SQLite** — default (`DB_CONNECTION=sqlite`), file at `database/database.sqlite`.
  - **MySQL / MariaDB** — via the `pdo_mysql` extension.
  - **PostgreSQL** — via `pdo_pgsql`.
  - **SQL Server** — via `pdo_sqlsrv`.
- **ramsey/uuid** `4.9.3` + **ramsey/collection** — UUID generation (a Laravel dependency).
- **brick/math** `0.14.8` — arbitrary-precision math, used by `ramsey/uuid`.

### 1.6 File Storage
- **league/flysystem** `3.35.3` + **league/flysystem-local** — Laravel's filesystem abstraction (`config/filesystems.php`), supporting the `local`, `public`, and `s3` disks.
- **league/mime-type-detection** — file MIME type detection.
- AWS S3 (optional, already configured in `config/filesystems.php`, not actively used by default).

### 1.7 Utilities & Framework Infrastructure
- **nesbot/carbon** `3.13.2` — date/time manipulation.
- **monolog/monolog** `3.10.0` — logging (`single`, `daily`, `slack`, `stderr`, etc. channels via `config/logging.php`).
- **symfony/console**, **symfony/http-foundation**, **symfony/http-kernel**, **symfony/routing**, **symfony/mailer**, **symfony/mime**, **symfony/process**, **symfony/finder**, **symfony/error-handler**, **symfony/var-dumper**, etc. — core components used internally by Laravel.
- **doctrine/inflector** — pluralization/singularization of table names & Eloquent relations.
- **dragonmantank/cron-expression** — cron schedule parsing (`Illuminate\Console\Scheduling`).
- **egulias/email-validator** — email format validation.
- **vlucas/phpdotenv** — loads variables from the `.env` file.
- **voku/portable-ascii** — ASCII string normalization (used by `Str::slug`, etc.).
- **league/commonmark** — Markdown parsing (used by `Illuminate\Mail\Markdown`).
- **nunomaduro/termwind** — Tailwind-style CLI output styling (used by Artisan & Pail).
- **psy/psysh** — the interactive REPL that powers `php artisan tinker`.

### 1.8 Mail, Queue, Cache, Session
- **Mail**: `log` driver by default (`config/mail.php`), with support for SMTP, SES, Postmark, Resend, Sendmail.
- **Queue**: `database` driver (`jobs`, `job_batches`, `failed_jobs` tables), also supports `redis`, `sqs`, `beanstalkd`, `sync`.
- **Cache**: `database` driver by default (`config/cache.php`), also supports `redis`, `memcached`, `dynamodb`, `file`, `array`.
- **Session**: `database` driver (`config/session.php`).
- Redis (`predis`/`phpredis`) is configured but optional (`config/database.php` → `redis`).

### 1.10 Backend Asset Build 
- **Vite** `^7.0.7` — bundler.
- **laravel-vite-plugin** `^2.0.0` — Vite ↔ Laravel integration (the `@vite()` Blade helper).
- **@tailwindcss/vite** `^4.0.0` + **tailwindcss** `^4.0.0` — styling.
- **axios** `^1.11.0` — Blade-side HTTP client (`resources/js/bootstrap.js`).
- **concurrently** `^9.0.1` — runs the server, queue listener, `pail`, and Vite in parallel via `composer run dev`.

---

## 2. Frontend — `frontend/` (Svelte SPA — Main Application)

### 2.1 Language & Runtime
- **JavaScript (ESM)** — `"type": "module"` in `package.json`, no TypeScript (though `checkJs: true` in `jsconfig.json` enables JS type-checking via JSDoc).

### 2.2 UI Framework
- **Svelte** `5.56.10` — the reactive component framework; uses **Svelte 5 Runes** (`$state`) for state management, seen in `sharedvar.svelte.js` and `Counter.svelte`.

### 2.3 Routing
- **svelte-spa-router** `5.1.1` — client-side hash-based routing (`/#/`, `/#/profile`, `/#/login`, etc.), defined in `App.svelte`.

### 2.4 State Management
- No external library (Redux/Pinia-like) is used — pure **Svelte 5 `$state` runes** are used in `frontend/src/lib/sharedvar.svelte.js` for global UI state (profile dropdown status, navbar menu, active page).

### 2.5 Communication with the Backend
- **Fetch API (native browser)** — used directly in `Profile.svelte`, `EditProfile.svelte`, `Timeline.svelte`, `Login.svelte` to call the backend REST API.
- **localStorage** — stores the `auth_token` (Sanctum bearer token) after a successful Google login.

### 2.6 Styling
- **Tailwind CSS** `4.3.3` + **@tailwindcss/vite** `4.3.3` — utility-first CSS, configured via a CSS-first `@theme` in `app.css` (instead of `tailwind.config.js`).
- **Custom neo-brutalist design**: custom color palette (`pale-purple`, `laser-pink`, `electric-cyan`, `cyber-lime`, `pale-red`, `off-white`, `pitch-black`, `neon-yellow`), thick borders + solid shadows (`shadow-[Npx_Npx_0_#000]`).
- **Google Fonts** (via CSS `@import`): Anton, Archivo, Bitcount Prop Single, Cal Sans, Imperial Script, Poppins, Space Mono.
- **Inline SVG** for all icons (no external icon library such as FontAwesome/Lucide, though a few `data-icon`/`fa-*` attributes remain from an old template).

### 2.7 Build Tool
- **Vite** `8.2.2` — dev server & production bundler.
- **@sveltejs/vite-plugin-svelte** `7.3.0` — Svelte ↔ Vite integration.
- Vite 8's internal bundler: **rolldown** (`~1.2.4`) — a Rust-based Rollup replacement.
- **lightningcss** — a Rust-based CSS processor (used by Tailwind v4 & Vite for CSS minification).

### 2.8 Editor/Tooling Configuration
- **jsconfig.json** — `bundler` module resolution, `ESNext` target, `checkJs: true` for JSDoc-based type-checking without full TypeScript.
- **.vscode/extensions.json** — recommends the `svelte.svelte-vscode` extension.

---

## 3. Cross-Layer Infrastructure & Configuration

### 3.1 CORS
- Configured in `backend/config/cors.php` — allows the `FRONTEND_URL` origin (default `http://localhost:5173`) to access the `api/*` path and `sanctum/csrf-cookie`.

### 3.2 Environment & Configuration
- **dotenv** (`vlucas/phpdotenv`) — the backend reads `.env`.
- **Vite env variables** (`import.meta.env.VITE_API_URL`) — the frontend reads `.env` with the `VITE_` prefix.

### 3.3 Version Control & Repo Tooling
- **Git** — version control (`.gitignore`, `.gitattributes` per sub-project).
- **.editorconfig** — indentation standard (4-space PHP, LF line ending) in `backend/`.

### 3.4 Static Server / Deployment
- **Apache `.htaccess`** (`backend/public/.htaccess`) — Laravel's built-in rewrite rule (front controller pattern, handles the `Authorization` & `X-XSRF-Token` headers).
- Laravel's built-in **health check route** at `/up` (registered in `bootstrap/app.php` via `health: '/up'`).

---

## 4. Summary Tables

### Backend — Production (`composer.json` → `require`)
| Package | Locked Version |
|---|---|
| php | ^8.2 |
| laravel/framework | v12.68.0 |
| laravel/sanctum | v4.3.3 |
| laravel/socialite | v5.31.0 |
| laravel/tinker | v2.11.1 |

### Backend — Development (`composer.json` → `require-dev`)
| Package | Locked Version |
|---|---|
| fakerphp/faker | v1.24.1 |
| laravel/pail | v1.2.7 |
| laravel/pint | v1.30.4 |
| laravel/sail | v1.67.0 |
| mockery/mockery | 1.6.15 |
| nunomaduro/collision | v8.9.5 |
| phpunit/phpunit | 11.5.56 |

### Frontend — Node toolchain (`frontend/package.json`)
| Package | Version |
|---|---|
| svelte | 5.56.10 |
| vite | 8.2.2 |
| @sveltejs/vite-plugin-svelte | 7.3.0 |
| svelte-spa-router | 5.1.1 |
| tailwindcss | 4.3.3 |
| @tailwindcss/vite | 4.3.3 |

---
