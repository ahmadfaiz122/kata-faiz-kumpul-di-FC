<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Daftar domain email yang diizinkan login.
     * Tambahkan domain lain di sini jika perlu (misal domain dosen/staf).
     */
    protected array $allowedDomains = [
        'mhs.unesa.ac.id',
    ];

    protected string $frontendUrl;

    public function __construct()
    {
        $this->frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
    }

    /**
     * Arahkan user ke halaman consent Google.
     * GET /auth/google/redirect
     */
    public function redirect()
    {
        return Socialite::driver('google')
            // 'hd' hanya HINT ke Google agar akun @unesa.ac.id lebih mudah dipilih,
            // BUKAN validasi sesungguhnya — validasi wajib tetap dilakukan di callback().
            ->with(['hd' => 'unesa.ac.id'])
            ->redirect();
    }

    /**
     * Callback dari Google setelah user memberi izin.
     * GET /auth/google/callback
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->away(
                $this->frontendUrl . '/login?error=' . urlencode('Gagal melakukan autentikasi dengan Google. Silakan coba lagi.')
            );
        }

        $email = $googleUser->getEmail();

        if (! $email || ! $this->isEmailDomainAllowed($email)) {
            return redirect()->away(
                $this->frontendUrl . '/login?error=' . urlencode(
                    'Login gagal. Hanya email mahasiswa UNESA (@mhs.unesa.ac.id) yang diizinkan.'
                )
            );
        }

        // Opsional tapi disarankan: pastikan Google sudah verifikasi email tsb.
        if (method_exists($googleUser, 'user') && isset($googleUser->user['email_verified']) && ! $googleUser->user['email_verified']) {
            return redirect()->away(
                $this->frontendUrl . '/login?error=' . urlencode('Email Google kamu belum terverifikasi.')
            );
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser->getName() ?: $googleUser->getNickname(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(32)), // password acak, login hanya lewat Google
            ]
        );

        // Membutuhkan Laravel Sanctum (php artisan install:api atau composer require laravel/sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->away($this->frontendUrl . '/login?token=' . urlencode($token));
    }

    /**
     * Cek apakah domain email termasuk dalam daftar yang diizinkan.
     */
    protected function isEmailDomainAllowed(string $email): bool
    {
        $domain = strtolower(Str::after($email, '@'));

        return in_array($domain, $this->allowedDomains, true);
    }
}
