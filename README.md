# LetSo

A web platform for university students to exchange skills using a **time banking credit system**. Login is restricted to campus email addresses (`@mhs.unesa.ac.id`) via Google OAuth.

This repository is a **monorepo** containing two separate applications that communicate via a REST API:

```text
frontend/  (Svelte 5 SPA, hash routing)
      |
      | REST API + Bearer token
      v
backend/   (Laravel 12 API, Sanctum, Socialite)
      |
      v
SQLite (default) / MySQL / MariaDB / PostgreSQL / SQL Server
```

## Features

- **Google OAuth Login** restricted to `@mhs.unesa.ac.id` email addresses, issuing a Laravel Sanctum token stored in `localStorage`; new profiles receive a **1 credit signup bonus**, recorded as a `signup_bonus` entry in the credit ledger.
- **Profile** — bio, social links, skills, and achievements.
- **Timeline** — posts, likes, and comments between students.
- **Skill Proposals & Transactions** — submit proposals, recruit mentors, and complete sessions via a credit or skill-barter mode.
- **Time credit system** — credit movements are recorded in a ledger for every approved/completed transaction.
- **Leaderboard & rating** — reputation is calculated from reviews after a transaction is completed; the leaderboard score is a weighted composite of rating, transaction volume, recent activity, and skill/partner diversity, with anti-collusion safeguards.


## Tech Stack

**Backend** — PHP 8.2+, Laravel 12 (`v12.68.0`), Laravel Sanctum `v4.3.3` (auth token), Laravel Socialite `v5.31.0` (Google OAuth), SQLite (default; MySQL/MariaDB/PostgreSQL/SQL Server supported), Eloquent ORM.

**Frontend** — Svelte `5.56.10` (with Runes), Vite `8.2.2`, `svelte-spa-router` `5.1.1` (hash routing), Tailwind CSS `4.3.3`, neo-brutalist design (thick borders + solid shadows).

**Communication** — REST API with Bearer token authentication (Sanctum). All `/api` routes are rate-limited (`throttle:60,1`), with a stricter `throttle:10,1` limit on upload endpoints. CORS is configured for the frontend origin (`FRONTEND_URL`, plus `http://localhost:5173` and `http://127.0.0.1:5173`), with `supports_credentials` disabled since auth is token-based rather than cookie-based.

## Directory Structure

```text
backend/
├── app/Http/Controllers/     # GoogleAuthController, ProfileController, etc.
├── app/Models/                # User, Profile, Skill, Achievement, Post, Transaction, etc.
├── app/Policies/               # PostPolicy, CommentPolicy
├── database/migrations/       # 24 migration files
├── routes/api.php
├── routes/web.php
└── config/

frontend/
├── src/App.svelte             # hash route map
├── src/main.js
├── src/app.css                # Tailwind v4 + design tokens
├── src/pages/                 # SPA pages (Timeline, Profile, Login, etc.)
└── src/lib/                   # shared components and state
```

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 20.19+ and npm
- Git

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/ahmadfaiz122/kata-faiz-kumpul-di-FC
cd kata-faiz-kumpul-di-FC
```

### 2. Set Up the Backend

```bash
cd backend
composer install
```

Create a `.env` file inside the `backend/` folder:

```env
APP_NAME=SkillSwapp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:5173

DB_CONNECTION=sqlite

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Then run:

```bash
php artisan key:generate
php artisan migrate
```

#### Set Up Google OAuth

1. Open [Google Cloud Console](https://console.cloud.google.com/) and create a new project.
2. Go to **APIs & Services → OAuth consent screen → Clients → Create Client**.
3. Choose the **Web application** type, then fill in:
   - Authorized JavaScript origins: `http://localhost:5173/`
   - Authorized redirect URIs: `http://localhost:8000/auth/google/callback`
4. Copy the `Client ID` and `Client Secret` you obtained into the `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` variables in `.env`.

### 3. Set Up the Frontend

```bash
cd frontend
npm install
```

Create a `.env` file inside the `frontend/` folder:

```env
VITE_API_URL=http://localhost:8000
```

## Running the Application

```bash
# Terminal 1 — backend
cd backend
php artisan serve

# Terminal 2 — frontend
cd frontend
npm run dev
```

Open `http://localhost:5173` in your browser.

## API Summary

All endpoints are prefixed with `/api` and (for endpoints that require login) are protected by the `auth:sanctum` middleware.

| Area | Main Endpoints |
|---|---|
| Auth | `GET /auth/google/redirect`, `GET /auth/google/callback` |
| User & Profile | `GET /api/user`, `GET/POST/PUT /api/profile` |
| Skills & Achievements | `GET/POST/DELETE /api/skills`, `GET/POST/DELETE /api/achievements` |
| Timeline | `GET/POST /api/posts`, `POST /api/posts/{post}/like`, `GET/POST /api/posts/{post}/comments` |
| Proposals | `GET/POST /api/proposals`, `GET/POST/DELETE /api/user/proposals` |
| Transactions & Credits | `GET/POST /api/transactions`, `POST /api/transactions/{id}/approve`, `GET /api/credits/ledger` |
| Leaderboard | `GET /api/leaderboard` |

## Security

- Email domain validation is performed on the server (`GoogleAuthController`), not only via the `hd` parameter on the Google redirect.
- The Sanctum token is sent as `Authorization: Bearer <token>`; `supports_credentials` in CORS is set to `false` since cookie-based auth is not used.
- All `/api` routes are protected by the `throttle:60,1` rate limit, with upload/review endpoints (`/skills`, `/achievements`, `/transactions/{id}/review`) additionally limited to `throttle:10,1`.

## Known Issues

- `POST /api/transactions/{transaction}/reject` and `POST /api/transactions/{transaction}/cancel` already exist in `TransactionController` and are already called by `Messages.svelte`, but are not yet registered in `routes/api.php` — the reject/cancel buttons in the UI will 404 until these routes are added.
- `Transaksi.svelte` and the weekly leaderboard widget in `Timeline.svelte` still use static/mock data and are not yet connected to the relevant backend endpoints.
- `CategoryBar1.svelte` is an old version of `CategoryBar.svelte` that is no longer used by any route but is still present in the repo.
