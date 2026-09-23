<script>
  import { onMount } from 'svelte';
  import { push } from 'svelte-spa-router';

  // Set VITE_API_URL di file .env frontend, contoh: VITE_API_URL=http://localhost:8000
  const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';

  let error = '';
  let loading = false;

  function loginWithGoogle() {
    loading = true;
    window.location.href = `${backendUrl}/auth/google/redirect`;
  }

  onMount(() => {
    const hashQuery = window.location.hash.includes('?')
      ? window.location.hash.slice(window.location.hash.indexOf('?') + 1)
      : '';
    const params = new URLSearchParams(window.location.search || hashQuery);
    const token = params.get('token');
    const errParam = params.get('error');

    if (token) {
      localStorage.setItem('auth_token', token);
      // Bersihkan query string lalu arahkan ke halaman utama/dashboard
      push('/');
      return;
    }

    if (errParam) {
      error = errParam;
      // Bersihkan query string agar pesan tidak muncul lagi saat refresh
      window.history.replaceState({}, document.title, '/#/login');
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
  :global(body) { margin: 0; }
  .page {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px;
    box-sizing: border-box;
    background-color: #d8d8d8;
    background-image: linear-gradient(#bdbdbd 1px, transparent 1px), linear-gradient(90deg, #bdbdbd 1px, transparent 1px);
    background-size: 24px 24px;
    font-family: "Space Mono", monospace;
  }
  .gate {
    width: 100%;
    max-width: 430px;
    border: 4px solid #000;
    border-radius: 0;
    background: #fffdf5;
    padding: 32px 28px 26px;
    text-align: center;
    box-shadow: 12px 12px 0 #000;
  }
  .gate-mark { display: inline-grid; place-items: center; width: 72px; height: 72px; margin-bottom: 18px; border: 3px solid #000; background: #b6ff00; color: #000; box-shadow: 5px 5px 0 #000; }
  .eyebrow { margin: 0 0 8px; color: #ff006e; font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
  h1 { margin: 0 0 10px; color: #000; font-family: "Anton", Impact, sans-serif; font-size: clamp(2.6rem, 10vw, 4rem); line-height: 0.95; text-transform: uppercase; }
  .sub { margin: 0 0 26px; color: #222; font-family: "Archivo", Arial, sans-serif; font-size: 13px; line-height: 1.55; }
  .sub code { border: 2px solid #000; background: #ffe477; padding: 2px 6px; color: #000; font-family: "Space Mono", monospace; font-size: 11px; }
  .google-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 12px; border: 3px solid #000; border-radius: 0; padding: 14px 18px; background: #fff; color: #000; font-family: "Space Mono", monospace; font-size: 12px; font-weight: 700; cursor: pointer; box-shadow: 5px 5px 0 #000; transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease; }
  .google-btn:hover:not(:disabled) { transform: translate(-2px, -2px); box-shadow: 7px 7px 0 #000; background: #8bd5ff; }
  .google-btn:active:not(:disabled) { transform: translate(2px, 2px); box-shadow: 2px 2px 0 #000; }
  .google-btn:disabled { cursor: progress; opacity: 0.55; }
  .error { margin: 18px 0 0; border: 3px solid #000; padding: 11px 14px; background: #ffb2c1; color: #000; font-size: 11px; font-weight: 700; line-height: 1.45; text-align: left; box-shadow: 3px 3px 0 #000; }
  .footnote { margin: 22px 0 0; color: #333; font-size: 10px; font-weight: 700; line-height: 1.5; }
  @media (max-width: 480px) { .page { padding: 18px; } .gate { padding: 28px 20px 22px; box-shadow: 8px 8px 0 #000; } }
</style>
