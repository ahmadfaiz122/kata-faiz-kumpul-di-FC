# Login Google OAuth 2.0 — Svelte + Laravel (Filter Domain UNESA)

Alur: **Svelte** menampilkan tombol "Masuk dengan Google" → redirect ke **Laravel** → Laravel redirect ke Google → Google callback ke Laravel → Laravel **cek domain email** → jika `@mhs.unesa.ac.id` valid, buat/ambil user + token Sanctum → redirect balik ke Svelte membawa token.

## Struktur
```
frontend/src/lib/Login.svelte        # Halaman login Svelte
backend/app/Http/Controllers/Auth/GoogleAuthController.php
backend/routes/web.php               # Tambahkan ke routes/web.php kamu
backend/config/services-snippet.php  # Tambahkan ke config/services.php kamu
backend/database/migrations/...      # Migration kolom google_id & avatar
```

## 1. Setup Backend (Laravel)

```bash
composer require laravel/socialite
php artisan install:api   # jika Sanctum belum terpasang, untuk token auth
```

1. Salin isi `GoogleAuthController.php` ke `app/Http/Controllers/Auth/GoogleAuthController.php`.
2. Tambahkan 2 route dari `routes/web.php` (file ini) ke `routes/web.php` project kamu.
3. Tambahkan blok `'google' => [...]` dari `config/services-snippet.php` ke `config/services.php`.
4. Jalankan migration: salin file migration ke folder `database/migrations/`, lalu:
   ```bash
   php artisan migrate
   ```
5. Isi `.env`:
   ```env
   GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   FRONTEND_URL=http://localhost:5173
   ```
6. Model `User` harus pakai trait `HasApiTokens` (dari Sanctum):
   ```php
   use Laravel\Sanctum\HasApiTokens;

   class User extends Authenticatable
   {
       use HasApiTokens;
       // ...
   }
   ```

### Cara filter domain bekerja
Di `GoogleAuthController::isEmailDomainAllowed()`, email dari Google dicek terhadap array `$allowedDomains = ['mhs.unesa.ac.id']`. Kalau domainnya bukan itu, user diarahkan balik ke frontend dengan pesan error, **tanpa** akun dibuat dan **tanpa** token diberikan. Parameter `hd` di method `redirect()` hanya mempermudah Google menyaring akun yang ditampilkan — tetap wajib divalidasi ulang di server karena `hd` bisa dilewati/dipalsukan dari sisi client.

## 2. Setup Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/) → buat project → **APIs & Services > Credentials**.
2. Buat **OAuth Client ID** tipe *Web application*.
3. **Authorized redirect URIs**: `http://localhost:8000/auth/google/callback` (sesuaikan domain saat production).
4. Salin Client ID & Client Secret ke `.env` Laravel.

## 3. Setup Frontend (Svelte)

```bash
npm create vite@latest frontend -- --template svelte
cd frontend
npm install
```

1. Salin `Login.svelte` ke `src/lib/Login.svelte`, lalu render di `src/App.svelte` atau route `/login`:
   ```svelte
   <script>
     import Login from './lib/Login.svelte';
   </script>

   <Login />
   ```
2. Buat file `.env` di root frontend:
   ```env
   VITE_API_URL=http://localhost:8000
   ```
3. Jalankan:
   ```bash
   npm run dev
   ```

## Alur token di frontend
Setelah Laravel redirect ke `http://localhost:5173/login?token=xxx`, `Login.svelte` otomatis menyimpan token ke `localStorage` (key `auth_token`) lalu redirect ke `/dashboard`. Sesuaikan tujuan redirect dan penyimpanan token (misal pakai cookie httpOnly + endpoint `/api/me` untuk keamanan lebih baik) sesuai kebutuhan production kamu.

## Catatan keamanan
- Ganti `localStorage` dengan **httpOnly cookie** kalau butuh proteksi XSS lebih ketat untuk production.
- Selalu validasi domain di **backend**, jangan pernah mengandalkan filter di sisi frontend saja.
- Tambahkan middleware `auth:sanctum` pada route API yang butuh login.
