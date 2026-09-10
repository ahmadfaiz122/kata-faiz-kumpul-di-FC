<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'hd' => 'mhs.unesa.ac.id',
                'prompt' => 'select_account', // selalu minta pilih akun, agar user bisa ganti akun jika salah login
            ])
            ->redirect();
    }

    /**
     * Callback dari Google setelah user memberi izin.
     * GET /auth/google/callback
     */
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->away(
                $this->frontendUrl . '/#/login?error=' . urlencode('Autentikasi Google dibatalkan.')
            );
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->away(
                $this->frontendUrl . '/#/login?error=' . urlencode('Gagal melakukan autentikasi dengan Google. Silakan coba lagi.')
            );
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if (! $email || ! $this->isEmailDomainAllowed($email)) {
            return redirect()->away(
                $this->frontendUrl . '/#/login?error=' . urlencode(
                    'Login gagal. Hanya email mahasiswa UNESA (@mhs.unesa.ac.id) yang diizinkan.'
                )
            );
        }

        // Google mengirim flag ini pada payload user; domain saja tidak cukup.
        if (isset($googleUser->user['verified_email']) && ! $googleUser->user['verified_email']) {
            return redirect()->away(
                $this->frontendUrl . '/#/login?error=' . urlencode('Email Google kamu belum terverifikasi.')
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Autentikasi Google berhasil.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'google_id' => $user->google_id,
                ],
                'google_profile' => $googleUser->user,
                'token' => $token,
            ]);
        }

        return redirect()->away($this->frontendUrl . '/#/login?token=' . urlencode($token));
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
