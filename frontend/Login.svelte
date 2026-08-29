<script>
  import { onMount } from 'svelte';

  // Set VITE_API_URL di file .env frontend, contoh: VITE_API_URL=http://localhost:8000
  const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';

  let error = '';
  let loading = false;

  function loginWithGoogle() {
    loading = true;
    window.location.href = `${backendUrl}/auth/google/redirect`;
  }

  onMount(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    const errParam = params.get('error');

    if (token) {
      localStorage.setItem('auth_token', token);
      // Bersihkan query string lalu arahkan ke halaman utama/dashboard
      window.location.href = '/dashboard';
      return;
    }

    if (errParam) {
      error = decodeURIComponent(errParam);
      // Bersihkan query string agar pesan tidak muncul lagi saat refresh
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  });
</script>

<div class="page">
  <div class="gate">
    <div class="gate-mark" aria-hidden="true">
      <svg viewBox="0 0 64 64" width="40" height="40">
        <path d="M8 54 L8 22 L32 8 L56 22 L56 54" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
        <line x1="4" y1="54" x2="60" y2="54" stroke="currentColor" stroke-width="3" />
        <line x1="16" y1="54" x2="16" y2="30" stroke="currentColor" stroke-width="2.5" />
        <line x1="32" y1="54" x2="32" y2="30" stroke="currentColor" stroke-width="2.5" />
        <line x1="48" y1="54" x2="48" y2="30" stroke="currentColor" stroke-width="2.5" />
      </svg>
    </div>

    <p class="eyebrow">Portal Akademik &middot; UNESA</p>
    <h1>Masuk ke akun kamu</h1>
    <p class="sub">Khusus mahasiswa aktif dengan email <code>@mhs.unesa.ac.id</code></p>

    <button class="google-btn" on:click={loginWithGoogle} disabled={loading}>
      <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
        <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z"/>
        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.6 18.9 13 24 13c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4c-7.5 0-14 4.1-17.7 10.7z"/>
        <path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 14-5.5l-6.5-5.4C29.4 34.8 26.8 36 24 36c-5.3 0-9.7-3.1-11.3-8l-6.5 5C9.8 39.8 16.4 44 24 44z"/>
        <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.3 5.7l6.5 5.4C39.9 37 44 31.5 44 24c0-1.3-.1-2.7-.4-3.5z"/>
      </svg>
      {loading ? 'Mengarahkan ke Google...' : 'Masuk dengan Google'}
    </button>

    {#if error}
      <p class="error" role="alert">{error}</p>
    {/if}

    <p class="footnote">Akses akan ditolak otomatis untuk email di luar domain mahasiswa UNESA.</p>
  </div>
</div>

<style>
  :global(body) {
    margin: 0;
  }

  .page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
      radial-gradient(circle at 15% 10%, rgba(201, 162, 39, 0.12), transparent 45%),
      radial-gradient(circle at 85% 90%, rgba(201, 162, 39, 0.10), transparent 45%),
      #0a2e5c;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    padding: 24px;
    box-sizing: border-box;
  }

  .gate {
    width: 100%;
    max-width: 380px;
    background: #f7f5f0;
    border-radius: 14px;
    padding: 40px 32px 32px;
    text-align: center;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(201, 162, 39, 0.35);
  }

  .gate-mark {
    color: #0a2e5c;
    margin-bottom: 12px;
  }

  .eyebrow {
    margin: 0 0 6px;
    font-size: 12px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #c9a227;
    font-weight: 600;
  }

  h1 {
    margin: 0 0 8px;
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 26px;
    color: #0a2e5c;
    font-weight: 700;
  }

  .sub {
    margin: 0 0 28px;
    font-size: 13.5px;
    color: #55606f;
    line-height: 1.5;
  }

  .sub code {
    background: rgba(10, 46, 92, 0.08);
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 12.5px;
    color: #0a2e5c;
  }

  .google-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 13px 18px;
    border-radius: 10px;
    border: 1px solid #d8dde3;
    background: #ffffff;
    color: #26313f;
    font-size: 14.5px;
    font-weight: 600;
    cursor: pointer;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
  }

  .google-btn:hover:not(:disabled) {
    box-shadow: 0 6px 16px rgba(10, 46, 92, 0.18);
    transform: translateY(-1px);
  }

  .google-btn:disabled {
    opacity: 0.7;
    cursor: progress;
  }

  .error {
    margin: 18px 0 0;
    padding: 10px 14px;
    background: rgba(198, 40, 40, 0.08);
    color: #b3261e;
    border: 1px solid rgba(198, 40, 40, 0.25);
    border-radius: 8px;
    font-size: 13px;
    text-align: left;
  }

  .footnote {
    margin: 22px 0 0;
    font-size: 11.5px;
    color: #8a93a0;
    line-height: 1.5;
  }
</style>
