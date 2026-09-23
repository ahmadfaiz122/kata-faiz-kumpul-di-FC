# Technical Documentation - LetSo

## 1. Architecture

```text
frontend/ (Svelte 5 SPA, hash routing)
        |
        | REST API + Bearer token
        v
backend/ (Laravel 12 API, Sanctum, Socialite)
        |
        v
SQLite default / MySQL, MariaDB, PostgreSQL, SQL Server
```

- The backend is a stateless Laravel API. The Google OAuth route lives on Laravel's web route.
- The frontend is a separate Svelte application in the `frontend/` folder and uses `svelte-spa-router`.
- Authentication uses Google OAuth for `@mhs.unesa.ac.id` emails. The callback issues a Laravel Sanctum token and grants a **1 credit bonus** to new profiles (recorded as a `credit_ledger` entry of type `signup_bonus`).
- The token is stored in `localStorage` under the key `auth_token` and sent as `Authorization: Bearer ...`.
- Avatar files, skill materials, achievement certificates, and proposals are stored on Laravel's `public` disk. Run `php artisan storage:link` so the `/storage/...` URL is accessible.
- All API routes fall under the `throttle:60,1` rate limit; upload endpoints (`/skills`, `/achievements`, `/transactions/{id}/review`) have an additional `throttle:10,1` rate limit.

## 2. Functional Directory Structure

```text
backend/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/GoogleAuthController.php
│   │   ├── ProfileController.php
│   │   ├── SkillController.php
│   │   ├── AchievementController.php
│   │   ├── PostController.php
│   │   ├── ProposalController.php
│   │   ├── TransactionController.php
│   │   ├── TransactionReviewController.php
│   │   └── LeaderboardController.php
│   ├── Models/ (User, Profile, Skill, Category, Achievement, Post, PostLike,
│   │           Comment, SkillRequest, Transaction, TransactionReview,
│   │           CreditLedger, PostTimeline)
│   ├── Policies/ (PostPolicy, CommentPolicy)
│   └── Providers/AppServiceProvider.php
├── database/migrations/ (24 migration files)
├── routes/api.php
├── routes/web.php
└── config/

frontend/
├── src/App.svelte                 # hash route map
├── src/main.js                    # mounts Svelte
├── src/app.css                    # Tailwind v4 and design tokens
├── src/pages/                     # SPA pages
└── src/lib/                       # shared components and state
```

## 3. Backend

### 3.1 Entry Point and Configuration

- `backend/public/index.php` is the HTTP front controller.
- `backend/artisan` is Laravel's CLI entry point.
- `backend/bootstrap/app.php` registers the web, API, console, and health-check routes.
- `backend/config/app.php` reads `FRONTEND_URL`, uses the `UTC` timezone, and the `en` locale.
- `backend/config/cors.php` allows `FRONTEND_URL`, `http://localhost:5173`, and `http://127.0.0.1:5173`; `supports_credentials` is `false` because authentication uses a Bearer token.
- `backend/config/sanctum.php` gives tokens no expiry (`expiration: null`).
- The default database is SQLite. MySQL, MariaDB, PostgreSQL, and SQL Server connections are also available.
- The default public file storage is `storage/app/public`; the default cache, session, and queue use the database driver per Laravel's configuration.

### 3.2 OAuth Route

| Method | Path | Handler | Notes |
|---|---|---|---|
| GET | `/auth/google/redirect` | `GoogleAuthController::redirect` | Redirects the user to Google's consent screen with a UNESA domain hint |
| GET | `/auth/google/callback` | `GoogleAuthController::callback` | Validates the email, creates/updates the user, issues a token, then redirects to the frontend |

`GoogleAuthController` still validates the email domain server-side via the `mhs.unesa.ac.id` whitelist; Google's `hd` parameter is only a hint. The callback also rejects unverified emails (`verified_email`). When a new profile is created (`firstOrCreate`), the system grants `credits => 1` and records it once in `credit_ledger` with type `signup_bonus`. Regular browsers are redirected to `FRONTEND_URL/#/login?token=...`, while JSON requests can receive the token, user data, and the raw Google payload in the JSON response.

### 3.3 API Contract

All of the following paths have the `/api` prefix and fall under the `throttle:60,1` rate limit.

#### User and Profile

| Method | Endpoint | Middleware | Function |
|---|---|---|---|
| GET | `/user` | Sanctum | Logged-in user and brief profile; `?details=1` includes skill and achievement records |
| GET | `/profile` | Sanctum | Retrieves user and profile data |
| POST, PUT | `/profile` | Sanctum | Updates the profile and uploads an avatar |
| GET | `/skills` | Sanctum | List of the user's skills with `published` status |
| POST | `/skills` | Sanctum, `throttle:10,1` | Creates or updates a skill and its PDF material |
| DELETE | `/skills/{id}` | Sanctum | Deletes a skill owned by the user |
| GET | `/achievements` | Sanctum | List of the user's achievements |
| POST | `/achievements` | Sanctum, `throttle:10,1` | Creates or updates an achievement and its certificate |
| DELETE | `/achievements/{id}` | Sanctum | Deletes an achievement owned by the user |
| GET | `/categories` | Public | List of skill categories (`id`, `name`, `slug`) for the category dropdown |

`POST/PUT /profile` accepts multipart data when an avatar is sent. The avatar must be an image of at most 2 MB. Validated fields: `name`, `avatar`, `username`, `alias`, `bio`, `email` (unique against other users), `linkedin`, `github`, `instagram`, `skills`, and `achievements`. `nim` is **not** sent by the client; its value is always derived automatically on the server from the `@mhs.unesa.ac.id` email domain (`ProfileController::nimFromEmail`). Sending a `skills` or `achievements` array via the profile endpoint uses a replace-all strategy and stores names only; full skill/achievement data (category, material, certificate, dates) is managed via the `/skills` and `/achievements` endpoints respectively.

`POST /skills` validation: `name` (max 100 characters), `category_id` (required, must exist in the `categories` table), `description` (optional, max 1000), and `material` (required when creating a new skill, a genuine PDF file of at most **5 MB**). The server verifies the file name, checks the real MIME type via `finfo`, validates the `%PDF-` ... `%%EOF` header/footer, and rejects any PDF containing `/JavaScript`, `/OpenAction`, `/Launch`, `/RichMedia`, or `/EmbeddedFile`. New skills are automatically `published`; the `GET /skills` endpoint only returns skills with `published` status.

`POST /achievements` validation: `name` and `levels` are required; `category` and `description` are optional (max 1000); `tanggal_terbit` (issue date) and `kadaluwarsa` (expiry date) are required dates (`kadaluwarsa` must be `after_or_equal` `tanggal_terbit`); `certificate` is required when creating a new achievement, and must be a genuine PDF/JPG/JPEG/PNG of at most **5 MB**. For images, the server validates a minimum dimension of 100x100 pixels; for PDFs, the server validates the file header/footer.

#### Timeline, Posts, Likes, and Comments

| Method | Endpoint | Middleware | Function |
|---|---|---|---|
| GET | `/posts` | Public | Latest feed, pagination of 20, user, like/comment counts, and the user's like status if logged in |
| POST | `/posts` | Sanctum | Creates a post, `content` max 2000 characters |
| GET | `/user/posts` | Sanctum | Retrieves the user's own posts |
| PUT | `/posts/{post}` | Sanctum | Edits a post owned by the user via `PostPolicy` |
| DELETE | `/posts/{post}` | Sanctum | Deletes a post owned by the user via `PostPolicy` |
| POST | `/posts/{post}/like` | Sanctum | Toggles a like and returns the status/count |
| GET | `/posts/{post}/comments` | Public | Retrieves the latest comments |
| POST | `/posts/{post}/comments` | Sanctum | Creates a comment, `content` max 1000 characters |

`PostController` uses eager loading for the user and `withCount` for likes/comments. The `PostLike` model has a unique post-user combination so a single user cannot create a duplicate like.

#### Categories and Skill Proposals

| Method | Endpoint | Middleware | Function |
|---|---|---|---|
| GET | `/proposals` | Public | Proposals with `pending` status, created within the last 24 hours, whose skill is `published`; supports filter queries |
| GET | `/proposals/categories` | Public | List of unique category names from active `pending` proposals (used by `CategoryBar`) |
| GET | `/proposals/{proposal}` | Public | Proposal detail along with the requester's profile |
| GET | `/proposals/{proposal}/file` | Public | Downloads the proposal PDF |
| POST | `/proposals` | Sanctum | Creates a new proposal using a skill owned by the user, plus a PDF |
| GET | `/user/proposals` | Sanctum | Proposals created by the user |
| POST | `/user/proposals/{id}` | Sanctum | Updates a proposal owned by the user |
| DELETE | `/user/proposals/{id}` | Sanctum | Deletes a proposal and its related file |

`GET /proposals` supports the following query strings: `search` (matches `skill_name`), `category` (matches `skill_category`), `credit_min`/`credit_max` (`hour` range), and `rating_min`/`rating_max` and `reputasi_min`/`reputasi_max` (`rating`/`reputation` range on the requester's profile). The frontend's `FilterDropdown.svelte` sends these parameters directly from the rating, reputation, and credit sliders on the Dashboard.

Creating a proposal validates the name, email, phone number in `08...` format, city, skill, start schedule (`available_at`, cannot be in the past), a description of at most 5000 characters, and a PDF of at most 5 MB. The skill must belong to the requester's profile. Proposals use the `requests` table and are stored with an initial `pending` status.

#### Transactions and Credit

| Method | Endpoint | Middleware | Function |
|---|---|---|---|
| GET | `/transactions` | Sanctum | List of transactions as either provider or requester; automatically completes transactions that have passed `ends_at` |
| GET | `/transactions/{transaction}` | Sanctum | Transaction detail for the transaction's participants |
| POST | `/transactions` | Sanctum | Submits a transaction in `credit` or `skill` mode |
| POST | `/transactions/{transaction}/approve` | Sanctum | Provider approves the transaction |
| GET | `/transactions/{transaction}/materials/{skill}` | Sanctum | Downloads skill material when the session is accessible |
| POST | `/transactions/{transaction}/review` | Sanctum, `throttle:10,1` | Requester submits a rating and review after the session is completed |
| GET | `/credits/ledger` | Sanctum | Credit mutation history, paginated by 20 |

> **Note:** `TransactionController` still has `reject()` and `cancel()` methods, and the frontend's `Messages.svelte` calls `POST /api/transactions/{id}/reject` and `POST /api/transactions/{id}/cancel`, but neither route is currently **registered** in `routes/api.php`. The reject/cancel buttons in the UI will fail (404) until these routes are added back.

`POST /transactions` accepts `proposal_id`, `mode` (`credit` or `skill`), and `requester_skill_id` for skill-barter mode. The duration is always locked to `hour = 1` and `credits = 1` (for `credit` mode) regardless of the proposal's `hour` value. A single requester cannot submit a second active/pending transaction for the same proposal, and proposals older than 24 hours or with a status other than `pending` are rejected.

Transaction statuses used:

```text
pending -> active -> completed
pending -> rejected
pending -> cancelled
pending -> expired
```

When the provider approves a `credit`-mode transaction, one credit is deducted from the requester and added to the provider within a database transaction using a row lock, then two entries are created in `credit_ledger` (`spent` and `earned`). If the requester's balance is insufficient, the request is rejected with status 422. If the related proposal has already expired (more than 1 day old) at approval time, the transaction is automatically given `expired` status. The session becomes `active`, with `approved_at`, `starts_at`, and `ends_at` set to one hour after the start. Active transactions automatically become `completed` once `ends_at` has passed (checked on every `index`/`show` call). Material can only be accessed by participants on an `active` or `completed` transaction after the start time; in `skill` mode, material from both parties (skill provider and skill requester) is available.

A review can only be created once by the requester after the transaction is `completed`. `rating` is an integer from 1-5, `reputation` is a numeric value from 1-100 (no longer the `sad`/`flat`/`smile` label), and `comment` is optional, max 1000 characters. The system rejects a new review if the reviewer has already reviewed the same provider within the last 7 days (to prevent rating spam). After a review is created, the provider's profile `rating` and `reputation` are recalculated as the average of all reviews the provider has ever received.

#### Leaderboard

| Method | Endpoint | Middleware | Function |
|---|---|---|---|
| GET | `/leaderboard` | Public | Top 50 users by composite score, sorted descending |

`LeaderboardController` only includes users with at least 3 `completed` transactions. The score is calculated from a weighted combination:

- **45%** average rating, Bayesian-smoothed (minimum basis of 5 reviews, pulled toward the global average when reviews are still few).
- **20%** transaction volume (`log(1 + number of transactions)` normalized against the most active user).
- **20%** activity velocity over the last 30 days (`recent_transactions / 30`, capped at a maximum of 1).
- **15%** diversity (a combination of unique skill count and unique partner count).

The final score is multiplied by an exponential decay factor based on time since last activity (`decay = exp(-days_since_last_activity / 90)`). Reviews that reciprocate within 7 days (mutually giving each other high ratings) are excluded from the rating calculation, and users whose pattern indicates collusion (the same partner pair rating each other ≥ 4.5 at least 3 times) are excluded entirely from the leaderboard.

### 3.4 Controllers

- `GoogleAuthController`: OAuth flow, domain whitelist, user creation/update, Sanctum token, signup credit bonus.
- `ProfileController`: profile reads, avatar upload, user/profile field updates, deriving `nim` from email, and replace-all of the skill/achievement name arrays.
- `SkillController`: CRUD for skills owned by the profile (relation to `Category`), PDF material upload/replacement up to 5 MB with content validation.
- `AchievementController`: achievement CRUD, image/PDF certificate upload up to 5 MB with content and dimension validation, plus the `validated_upload` flag.
- `PostController`: feed, post CRUD, like toggle, and comments.
- `ProposalController`: listing of active proposals with search/category/rating/reputation/credit filters, proposal category list, detail, CRUD of the user's own proposals, and PDF download.
- `TransactionController`: transaction creation, status lifecycle (including automatic completion of expired transactions), credit transfer, ledger, and material access.
- `TransactionReviewController`: validation (including the 7-day per-partner cooldown) and storage of reviews for completed transactions, then recalculating the provider's rating/reputation.
- `LeaderboardController`: calculates the anti-collusion composite score and returns the top 50 users.

### 3.5 Models and Relations

| Model | Table | Main relations |
|---|---|---|
| `User` | `users` | `posts`, `profile`, `creditLedger` |
| `Profile` | `profiles` | `user`, `skillRecords`, `achievementRecords`, timeline posts |
| `Skill` | `skills` | `profile`, `category`; used by proposals and transaction materials |
| `Category` | `categories` | referenced by `Skill.category_id` |
| `Achievement` | `achievements` | `profile` |
| `Post` | `posts` | `user`, `likes`, `comments` |
| `PostLike` | `post_likes` | `post`, `user` |
| `Comment` | `comments` | `post`, `user` |
| `PostTimeline` | `post_timelines` | `profile`, for the old timeline structure |
| `SkillRequest` | `requests` | `skill`, `requesterUser` |
| `Transaction` | `transactions` | proposal, provider, requester, requester's skill, review |
| `TransactionReview` | `transaction_reviews` | transaction, reviewer, reviewed user |
| `CreditLedger` | `credit_ledger` | user, transaction |

Key fields:

- `Profile`: identity, bio, social links (`linkedin`, `github`, `instagram`), `rating` (average review, decimal), `reputation` (average review 1-100, decimal), `leaderboard`, `credits`, and legacy JSON `skills`/`achievements`.
- `Skill`: `name`, `category_id` (FK to `categories`), `category_skills` (legacy label), `description`, `material_path`, `status` (`published` by default).
- `Category`: `name`, `slug` (unique); seeded with six default categories (Education, Technology, Business, Language, Art, Writing).
- `Achievement`: `name`, `levels`, `category`, `description`, `tanggal_terbit` and `kadaluwarsa` (now `date` columns, no longer an integer `YYMM`), `certificate_path`, `validated_upload`.
- `SkillRequest`: requester, contact, city, `skill_id`, skill name/category/description, `proposal_path`, status, duration, and `available_at`.
- `Transaction`: proposal, provider/requester, `requester_skill_id`, mode, duration, credit, status, and lifecycle timestamps.
- `CreditLedger`: user, transaction, amount, balance after mutation, `type` (`spent`, `earned`, or `signup_bonus`), and description.

### 3.6 Migrations and Schema

Migrations run based on the file name's timestamp. Besides Laravel's built-in migrations, the main domain changes are:

1. Google fields on `users`: nullable unique `google_id` and `avatar`.
2. Sanctum personal access token.
3. `profiles` and basic profile fields, followed by rating/reputation/leaderboard/credits/social media fields.
4. `posts` with a cascading user foreign key.
5. `skills`, `achievements`, `post_timelines`, `requests`, and `transactions`.
6. `instagram` on profiles.
7. `post_likes` with a unique `(post_id, user_id)` constraint, and `comments`.
8. Achievement certificate/metadata fields.
9. `material_path` on skills.
10. Proposal fields on `requests`: identity, contact, skill, description, file, and schedule.
11. Transaction fields: requester skill, mode, approval, start/end schedule, and completion time.
12. `transaction_reviews` and `credit_ledger`.
13. `reputation` normalization: qualitative labels (`sad`/`flat`/`smile`) in `transaction_reviews` are converted into numbers 25/50/100, the `reputation` column's type is changed to numeric, and `profiles.reputation` is changed from an integer into a decimal average.
14. The `tanggal_terbit`/`kadaluwarsa` columns on `achievements` are changed from an integer `YYMM` into genuine `date` columns, with automatic legacy data migration.
15. A `categories` table (seeded with six categories) and a `skills.category_id` column (nullable foreign key, `nullOnDelete`), backfilled from the existing `category_skills`.
16. A `skills.status` column (default `published`, indexed) to control skill visibility in public listings.

The repository currently has 24 migration files, including one `profile.php` migration that is intentionally a no-op to preserve history. The transaction migration granted profiles an initial credit balance according to the implementation in effect when that migration was run. Some domain migrations use `hasTable`/`hasColumn` checks to maintain compatibility with databases that have already been migrated.

## 4. Frontend

### 4.1 Entry Point and Routing

`frontend/src/main.js` mounts `App.svelte`. `App.svelte` uses hash routing with the following map:

| Route | Component | Data status |
|---|---|---|
| `/` | `Timeline.svelte` | Posts API |
| `/swapp` | `Dashboard.svelte` | Proposals API, search, filter |
| `/timeline` | `Timeline.svelte` | Posts API |
| `/leaderboard` | `Leaderboard.svelte` | Leaderboard API (`/api/leaderboard`) |
| `/profile` | `Profile.svelte` | Profile, posts, proposals API |
| `/login` | `Login.svelte` | Google OAuth |
| `/edit-profile` | `EditProfile.svelte` | Profile, skill, category, achievement API |
| `/add-proposal` | `AddProposal.svelte` | Proposals API + PDF upload |
| `/rekrut/:id` | `Rekrut.svelte` | Proposal detail + transaction API |
| `/rekrut` | `Rekrut.svelte` | No ID, shows a request error |
| `/messages` | `Messages.svelte` | Transactions + ledger API |
| `/landing` | `LandingPage.svelte` | Landing page; some data is static |
| `/transaksi` | `Transaksi.svelte` | Static/mock, not yet fetching the API |

### 4.2 Page Flow

- `Login.svelte` reads `token` or `error` from the OAuth callback hash, stores the token, then redirects to `/`.
- `Timeline.svelte` fetches the public feed, creates posts, toggles likes, loads and submits comments, and edits/deletes the user's own posts. Its sidebar's weekly leaderboard widget still uses static data (`Kastama`, `Ibna`, `Reinzal`), separate from the `Leaderboard.svelte` page which is already connected to the API.
- `Dashboard.svelte` fetches proposals from `/api/proposals` with `search`, `category`, and rating/reputation/credit filters from `FilterDropdown.svelte`; search and filtering are fully connected to the backend.
- `Profile.svelte` fetches the user/profile, displays skills and achievements, and manages the profile's social section.
- `EditProfile.svelte` fetches the category list from `/api/categories` for the skill dropdown, and sends profile and file changes via the API. Full skill/achievement management uses dedicated endpoints; the certificate and material fields are required only when creating a new entry.
- `AddProposal.svelte` fetches the user and skills, validates the WhatsApp number and PDF (max 5 MB) on the client side, then sends a `FormData` to `/api/proposals`.
- `Rekrut.svelte` fetches proposal detail. The user chooses credit payment or skill barter and creates a transaction at `/api/transactions`.
- `Messages.svelte` separates received/sent transactions, provides approve, reject, cancel (see the note in section 3.3 regarding the unregistered `reject`/`cancel` routes), WhatsApp/material access, review, and credit history.
- `Transaksi.svelte` only renders a hardcoded transaction array for the prototype and is not yet connected to the `/api/transactions` endpoint.
- `Leaderboard.svelte` fetches data from `/api/leaderboard` and displays a top-3 podium plus a list of the following ranks; this page no longer uses mock data.

### 4.3 Components

| Component | Function/status |
|---|---|
| `Navbar.svelte` | Main responsive navigation |
| `ProfileDropdown.svelte` | User menu, profile navigation, and logout |
| `CategoryBar.svelte` | Category filter on the Swapp view; fetches the category list from `/api/proposals/categories` with a fallback of six static categories |
| `FilterDropdown.svelte` | Rating, reputation, and credit filter sliders; its parameters match the `GET /api/proposals` query |
| `SkillCard.svelte` | Proposal/skill card presentation; the "recruit" button links to `/#/rekrut/{id}` |
| `TimelinePost.svelte` | Post card with like, comment, edit, and delete callbacks |
| `profileComponents/*` | `Bio`, `Credentials`, `Index`, and `Achievement` for the profile |
| `CategoryBar1.svelte` | Old/inactive version, categories still hardcoded |
| `Card.svelte`, `Counter.svelte` | Prototype/boilerplate, not used by the main routes |
| `sharedvar.svelte.js` | Global UI dropdown/menu state using Svelte 5 runes |
| `alerts.js` | SweetAlert2 wrapper (`alertSuccess`, `alertError`, `confirmAction`) used for delete/save actions in Profile, EditProfile, and Messages |

### 4.4 Styling and Build

- `frontend/src/app.css` uses Tailwind CSS 4 via `@tailwindcss/vite`, neon color tokens, Google fonts, and enter/hover animations.
- The main visual style is neo-brutalist: thick black borders, solid shadows, and contrasting colors.
- `frontend/vite.config.js` loads the Svelte and Tailwind plugins.
- `frontend/svelte.config.js` does not add a preprocessor.
- `frontend/jsconfig.json` uses `moduleResolution: bundler`, target `ESNext`, and `checkJs: true`.
- The frontend reads `VITE_API_URL`, falling back to `http://localhost:8000`; `.env.production` points it to the production Hostinger domain.
- The backend uses `FRONTEND_URL`, falling back to `http://localhost:5173`.
