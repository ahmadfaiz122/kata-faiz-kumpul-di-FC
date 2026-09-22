<script>
    import symbol from "../assets/logo.webp";
    import { onMount } from 'svelte';

    // ---------------------------------------------------------------- auth
    const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';

    let loggedIn = $state(false);
    let activeNav = $state('cara-kerja');

    onMount(() => {
        loggedIn = !!localStorage.getItem('auth_token');
    });

    // Same flow as Login.svelte: Laravel -> Google -> callback -> /#/login?token=...
    function startNow() {
        window.location.href = loggedIn ? '/#/' : `${backendUrl}/auth/google/redirect`;
    }

    // svelte-spa-router uses the URL hash, so #anchors would change the route.
    // Scroll with JS instead of <a href="#id">.
    function goTo(id) {
        activeNav = id;
        const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        document.getElementById(id)?.scrollIntoView({ behavior: calm ? 'auto' : 'smooth', block: 'start' });
    }

    // ------------------------------------------------------ shared classes
    const wrap = 'mx-auto w-full max-w-348 px-5 sm:px-10 lg:px-14';
    const focus = 'focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-pitch-black';
    const pixel = "font-['Bitcount_Prop_Single']";
    const h2 = 'font-anton text-[clamp(2.6rem,7vw,5.25rem)] uppercase leading-[.92]';
    const eyebrow = 'mb-2.5 font-mono text-[13px] uppercase tracking-[.06em] text-laser-pink sm:text-base';
    const box = 'border-[3px] border-black shadow-[7px_7px_0_#000]';
    const iconBox = 'grid h-15 w-15 shrink-0 place-items-center border-[3px] border-black bg-white shadow-[4px_4px_0_#000]';
    const avatar = 'grid shrink-0 place-items-center rounded-full border-2 border-black bg-[#8d6e63] text-white';
    const stairs = '[clip-path:polygon(35%_0,100%_0,100%_100%,0_100%,0_78%,15%_78%,15%_58%,30%_58%,30%_35%,45%_35%,45%_0)]';

    // ------------------------------------------------------------ content
    const navItems = [
        { id: 'cara-kerja', label: 'Cara kerja' },
        { id: 'fitur', label: 'Fitur' },
    ];

    const perks = ['Gratis, tanpa uang', 'Kredit awal langsung masuk', 'Email @mhs.unesa.ac.id'];

    const categories = [
        { key: 'education', name: 'Education', bg: 'bg-[#ffa174]', hint: 'Materi kuliah, tugas, skripsi' },
        { key: 'technology', name: 'Technology', bg: 'bg-laser-pink', hint: 'Coding, web, olah data' },
        { key: 'business', name: 'Business', bg: 'bg-[#9b82e6]', hint: 'Pitch, laporan, brand strategy' },
        { key: 'language', name: 'Language', bg: 'bg-[#40b8d0]', hint: 'Inggris, Jepang, TOEFL' },
        { key: 'art', name: 'Art', bg: 'bg-[#ffe477]', hint: 'Poster, ilustrasi, editing video' },
        { key: 'writing', name: 'Writing', bg: 'bg-[#ffa174]', hint: 'Esai, copywriting, proofreading' }
    ];

    const without = [
        'Jasa desain, coding, atau editing di luar kampus terlalu mahal untuk kantong mahasiswa.',
        'Bantuan gratis dari teman tidak terorganisir dan rawan salah paham soal balas budi.',
        'Skill kamu nganggur, padahal ada teman sekampus yang lagi butuh.'
    ];
    const withLetso = [
        'Bayar pakai jam, bukan rupiah. 1 jam bantuan sama dengan 1 kredit.',
        'Setiap sesi tercatat dan dikonfirmasi berdua, jadi adil untuk kedua pihak.',
        'Skill kamu berubah jadi kredit yang bisa dipakai untuk belajar hal baru.'
    ];

    const steps = [
        { n: 1, title: 'Masuk dengan Google', text: 'Login pakai email @mhs.unesa.ac.id. Akunmu langsung dapat kredit awal gratis.', cls: 'bg-[#ffe477] lg:mt-[114px]', google: true },
        { n: 2, title: 'Pasang skill kamu', text: 'Tulis skill yang bisa kamu ajarkan dan yang ingin kamu pelajari. Profilmu tampil di Swapp.', cls: 'bg-pale-purple lg:mt-[76px]' },
        { n: 3, title: 'Rekrut dan atur jadwal', text: 'Pilih mentor, ajukan sesi, lalu sepakati waktu dan format online atau offline lewat chat.', cls: 'bg-electric-cyan lg:mt-[38px]' },
        { n: 4, title: 'Konfirmasi berdua', text: 'Kredit pindah setelah kedua pihak konfirmasi sesi selesai. Habis itu, saling kasih rating.', cls: 'bg-laser-pink text-off-white lg:mt-0' }
    ];

    // Ledger: saldo is computed so the numbers on the page can never disagree.
    const txnStyles = {
        mengajar: { label: 'Mengajar', badge: 'bg-[#2fc7b8]', icon: 'up' },
        belajar: { label: 'Belajar', badge: 'bg-[#ffa174]', icon: 'down' },
        barter: { label: 'Barter skill', badge: 'bg-pale-purple', icon: 'swap' }
    };
    const ledger = [
        { type: 'mengajar', title: 'PHP Dasar', partner: 'Budi Santoso', date: '16 Sep 2026, 16.00', delta: 1 },

        { type: 'belajar', title: 'Bahasa Inggris (TOEFL)', partner: 'Sarah Amelia', date: '14 Sep 2026, 10.00', delta: -1 },

        { type: 'barter', title: 'Copywriting', partner: 'Reza Pratama', swapFor: 'Editing Video', date: '11 Sep 2026, 09.00', delta: 0 }

    ];

    const balance = ledger.reduce((sum, row) => sum + row.delta, 0);

    const flow = ['Diajukan', 'Pengajar setuju', 'Pelajar setuju', 'Kredit pindah'];

    const faq = [
        { q: 'Apakah LetSo gratis?', a: 'Ya. Di LetSo tidak ada transaksi uang. Semua pakai kredit waktu, dan akun baru mendapat kredit awal gratis.' },
        { q: 'Siapa yang bisa daftar?', a: 'Mahasiswa aktif UNESA dengan email @mhs.unesa.ac.id. Email di luar domain itu otomatis ditolak.' },
        { q: 'Kalau kreditku habis?', a: 'Ajarkan skill kamu ke teman lain. Setiap 1 jam sesi yang selesai menambah 1 kredit.' },
        { q: 'Kalau sesinya batal?', a: 'Kredit baru berpindah setelah kedua pihak konfirmasi sesi selesai, jadi tidak ada yang dirugikan.' }
    ];

    const sdgs = [
        { n: '4', bg: 'bg-[#ffe477]', text: 'Pendidikan berkualitas' },
        { n: '8', bg: 'bg-[#ffa174]', text: 'Pekerjaan layak, tanpa eksploitasi' },
        { n: '10', bg: 'bg-electric-cyan', text: 'Berkurangnya kesenjangan' }
    ];

    // --------------------------------------------------------------- icons
    const paths = {
        star: '<polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>',
        trophy: '<path d="M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M7 6H4v2a3 3 0 0 0 3 3"/><path d="M17 6h3v2a3 3 0 0 1-3 3"/><path d="M12 14v4M8 21h8M9 18h6"/>',
        chat: '<path d="M4 4h16v12H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
        db: '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        check: '<path d="M4 12l5 5L20 6"/>',
        x: '<path d="M5 5l14 14M19 5L5 19"/>',
        ne: '<path d="M7 17L17 7M17 7H9M17 7v8"/>',
        clock: '<path d="M12 6v6l4 3"/>',
        shield: '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5L16 9.5"/>',
        people: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-4 3-6 6.5-6s6.5 2 6.5 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17 14c3 0 4.5 2 4.5 5"/>',
        up: '<path d="M12 19V6"/><path d="M6 11l6-6 6 6"/>',
        down: '<path d="M12 5v13"/><path d="M6 13l6 6 6-6"/>',
        swap: '<path d="M3 7h14"/><path d="M13 3l4 4-4 4"/><path d="M21 17H7"/><path d="M11 21l-4-4 4-4"/>'
    };

    // Category icons (64x64 grid), redrawn to match the reference image.
    const catIcons = {
        education: '<path d="M3 26 L32 12 L61 26 L32 40 Z"/><path d="M15 33 V45 C15 50 23 54 32 54 C41 54 49 50 49 45 V33"/><path d="M57 29 V44"/><path d="M53 44 H61"/>',
        technology: '<g stroke-width="7"><path d="M22 18 L8 32 L22 46"/><path d="M42 18 L56 32 L42 46"/><path d="M37 12 L27 52"/></g>',
        business: '<path d="M12 8 H34 L46 20 V47 H12 Z"/><path d="M34 8 V20 H46"/><rect x="17" y="24" width="13" height="10"/><path d="M34 26 H41 M34 31 H41 M17 41 H32"/><circle cx="46" cy="45" r="10" fill="#9b82e6"/><path stroke-width="2.6" d="M50 41 C49 38.5 42 38.5 42 42 C42 46 50 44 50 48.5 C50 52 43 52 41.5 49 M46 37 V54"/>',
        language: '<path d="M5 14 H31 M18 8 V14"/><path d="M11 14 C13 26 21 33 31 37"/><path d="M27 14 C25 26 16 33 6 37"/><g stroke-width="3.4"><path d="M33 56 L44 27 L55 56"/><path d="M37 47 H51"/></g>',
        art: '<path fill="#111" stroke-width="2" d="M32 6 C16 6 5 17 5 31 C5 46 17 58 30 58 C35 58 37 54 36 50 C35 46 38 43 42 43 H49 C56 43 60 38 60 31 C60 17 48 6 32 6 Z"/><g fill="#ffe477" stroke="none"><circle cx="19" cy="27" r="4"/><circle cx="31" cy="19" r="4"/><circle cx="45" cy="22" r="4"/><circle cx="21" cy="41" r="4"/><circle cx="47" cy="31" r="3.4"/></g>',
        writing: '<path d="M14 46 L18 33 L45 6 L56 17 L29 44 Z"/><path d="M40 11 L51 22"/><path d="M18 33 L29 44"/><path d="M14 46 L21 44"/><path stroke-width="3.4" d="M6 58 H58"/>'
    };
</script>

<!-- ===================================================== reusable snippets -->

{#snippet icon(name, cls = 'h-5 w-5')}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class={cls} aria-hidden="true">{@html paths[name]}</svg>
{/snippet}

<!-- WhatsApp logo glyph (Simple Icons, CC0). Colour it with a text-* class. -->
{#snippet whatsapp(cls = 'h-6 w-6')}
    <svg viewBox="0 0 24 24" fill="currentColor" class={cls} aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
{/snippet}

{#snippet googleG(cls = 'h-5 w-5')}
    <svg viewBox="0 0 48 48" class={cls} aria-hidden="true">
        <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z" />
        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.6 18.9 13 24 13c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4c-7.5 0-14 4.1-17.7 10.7z" />
        <path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 14-5.5l-6.5-5.4C29.4 34.8 26.8 36 24 36c-5.3 0-9.7-3.1-11.3-8l-6.5 5C9.8 39.8 16.4 44 24 44z" />
        <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.3 5.7l6.5 5.4C39.9 37 44 31.5 44 24c0-1.3-.1-2.7-.4-3.5z" />
    </svg>
{/snippet}

<!-- Swap the text for your real logo image if you have one: <img src="/logo.png" alt="LetSo" class="h-12" /> -->
{#snippet logo(size = 'text-[40px] sm:text-[54px]')}
    <span class="relative inline-block py-1.5 pl-2 pr-3.5 pb-2 font-['Erica_One'] leading-none text-white [-webkit-text-stroke:2px_#000] [paint-order:stroke_fill] [text-shadow:3px_3px_0_#ff006e,4px_4px_0_#000] {size}">
        <span aria-hidden="true" class="absolute -left-1 -top-3.5 h-3 w-6.5 -rotate-4 border-2 border-black bg-neon-yellow"></span>
        <span aria-hidden="true" class="absolute -right-1.5 -top-2 grid h-6 w-6 place-items-center rounded-full border-2 border-black bg-neon-yellow font-mono text-lg font-bold leading-none text-black [-webkit-text-stroke:0] [text-shadow:none]">+</span>
        LetSo
    </span>
{/snippet}

{#snippet googleBtn(cls, gcls, short = false)}
    <button type="button" onclick={startNow} class="button-lift inline-flex items-center whitespace-nowrap rounded-full border-[3px] border-black font-bold {focus} {cls}">
        <span class="grid bg-white shrink-0 place-items-center rounded-full border-2 border-black {gcls}">{@render googleG('h-[55%] w-[55%]')}</span>
        {#if short}
            <span class="sm:hidden">Masuk</span>
            <span class="hidden sm:inline">Masuk dengan Google</span>
        {:else}
            Masuk dengan Google
        {/if}
    </button>
{/snippet}

<!-- ============================================================== page -->

<main class="dashboard-enter overflow-x-clip bg-[#d8d8d8] font-archivo text-lg text-pitch-black">

    <!-- ============================== HERO ============================== -->
    <div class="relative overflow-hidden pb-16 lg:pb-[70px]">
        <div aria-hidden="true" class="absolute bottom-0 right-0 h-[520px] w-[92%] bg-[#ffa174] sm:w-[75%] xl:bottom-auto xl:top-8.5 xl:h-160 xl:w-190 {stairs}"></div>

        <div class={wrap}>
            <!-- header -->
            <header class="relative z-30 flex h-[100px] items-center justify-between md:grid md:h-[118px] md:grid-cols-2"> 
            <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-18 sm:w-42">
            <img src={symbol} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
            </a>
            <div class="md:justify-self-end"> 
                {@render googleBtn('bg-electric-cyan gap-3 py-2.5 pl-3.5 pr-5 text-base shadow-[6px_6px_0_#000] sm:pr-6 sm:text-[17px]', 'h-8.5 w-8.5', true)} 
            </div> 
            </header>

            <div class="grid items-center gap-6 pt-6 xl:grid-cols-[650px_1fr] xl:gap-2.5 xl:pt-9">
                <!-- left column -->
                <div>
                    <div class="mb-7 inline-flex items-center gap-2.5 border-[3px] border-black bg-off-white px-4 py-1.5 font-mono text-[13px] shadow-[4px_4px_0_#000] sm:text-[15px]">
                        <i class="block h-3 w-3 border-2 border-black bg-neon-yellow"></i>
                        Platform barter skill untuk mahasiswa UNESA
                    </div>

                    <h1 class="mb-7 flex flex-col items-start font-anton text-[clamp(3rem,13vw,7rem)] uppercase leading-none">
                        <span class="-ml-1.5 inline-block -rotate-[2.2deg] whitespace-nowrap border-[3px] border-black bg-laser-pink px-5 pb-1.5 pt-2.5 text-off-white shadow-[8px_8px_0_#000] sm:px-6.5">Tukar skill,</span>
                        <span class="ml-6 mt-3.5 inline-block rotate-[1.4deg] whitespace-nowrap border-[3px] border-black bg-[#2fc7b8] px-5 pb-1.5 pt-2.5 shadow-[8px_8px_0_#000] sm:ml-8.5 sm:px-6.5">bukan uang.</span>
                    </h1>

                    <p class="mb-8 max-w-150 text-lg leading-normal sm:text-[23px]">
                        Ajarkan yang kamu bisa selama 1 jam, dapat
                        <b class="whitespace-nowrap bg-neon-yellow px-1.5">1 kredit</b>.
                        Pakai kredit itu untuk belajar skill yang belum kamu kuasai dari teman satu kampus.
                    </p>

                    <div class="mb-8 flex flex-wrap items-center gap-x-6 gap-y-5">
                        {@render googleBtn('bg-laser-pink gap-3.5 py-3.5 pl-4 pr-7 text-lg text-off-white shadow-[8px_8px_0_#000] sm:pr-8.5 sm:text-[23px]', 'h-9 w-9 bg-white sm:h-11 sm:w-11')}
                        <button type="button" onclick={() => goTo('cara-kerja')} class="button-lift rounded-full border-[3px] border-black bg-off-white px-7 py-3.5 text-lg font-bold shadow-[6px_6px_0_#000] sm:text-xl {focus}">Lihat cara kerja</button>
                    </div>

                    <ul class="flex flex-wrap gap-x-5 gap-y-2 font-mono text-[13px]">
                        {#each perks as perk}
                            <li class="flex items-center gap-2.5">
                                <span class="grid h-5.5 w-5.5 place-items-center rounded-full border-2 border-black bg-[#4ade80]">{@render icon('check', 'h-3 w-3')}</span>
                                {perk}
                            </li>
                        {/each}
                    </ul>
                </div>

                <!-- right column: skill card illustration -->
                <div aria-hidden="true" class="relative mx-auto w-full max-w-170 pb-16 pt-20 xl:pt-24">
                    <!-- bell -->
                    <div class="absolute left-[24%] top-3 z-10 grid h-14 w-14 -rotate-6 place-items-center rounded-full border-[3px] border-black bg-white shadow-[5px_5px_0_#000] sm:h-16 sm:w-16">
                        {@render icon('chat', 'h-6 w-6 sm:h-7 sm:w-7')}
                        <em class="absolute -right-1.5 -top-1.5 grid h-6 w-6 place-items-center rounded-full border-2 border-black bg-laser-pink font-mono text-[13px] font-bold not-italic text-off-white">1</em>
                    </div>
                    <!-- credit pill -->
                    <div class="absolute right-0 top-6 z-10 flex rotate-[5deg] items-center gap-2.5 rounded-full border-[3px] border-black bg-neon-yellow py-2.5 pl-3.5 pr-5 font-mono text-xl font-bold shadow-[6px_6px_0_#000] sm:text-2xl">
                        <span class="grid h-9 w-9 place-items-center rounded-full border-[3px] border-black bg-white sm:h-10.5 sm:w-10.5">{@render icon('db', 'h-5 w-5 sm:h-[22px] sm:w-[22px]')}</span>
                        +1 kredit
                    </div>

                    <!-- the card -->
                    <div class="relative -rotate-[2.5deg] rounded-[30px] border-4 border-black bg-[#ece9e0] p-4 shadow-[12px_12px_0_#000] sm:grid sm:grid-cols-[1fr_200px] sm:gap-5 sm:p-6 sm:pl-5.5">
                        <!-- left: duration + skill -->
                        <div class="relative pb-4 pt-1 sm:border-r-[3px] sm:border-black sm:pr-5">
                            <span class="absolute left-0 top-0 z-10 grid h-9.5 w-9.5 place-items-center rounded-full border-[3px] border-black bg-[#f5c518]">{@render icon('clock', 'h-[18px] w-[18px]')}</span>
                            <div class="{pixel} ml-4 mt-8 -rotate-3 border-[3px] border-black bg-[#e8265f] px-5 py-3 text-[clamp(1.9rem,3vw,3.6rem)] font-silk font-bold uppercase leading-none text-white shadow-[5px_5px_0_#000] sm:pl-8.5">1 hour</div>
                            <div class="{pixel} relative z-10 -mt-2.5 ml-6 inline-block -rotate-3 bg-[#0b0b0b] px-4 py-1 text-[15px] uppercase tracking-[.14em] text-white">learning</div>
                            <div class="{pixel} -mt-1 ml-9 -rotate-3 border-[3px] border-black bg-[#3fd6c4] px-5 py-3 text-[clamp(2.2rem,8vw,4.4rem)] font-silk font-bold uppercase leading-none shadow-[5px_5px_0_#000] sm:pl-8.5">php</div>
                            <p class="{pixel} mt-6 flex items-center gap-1 text-[13px] tracking-[.1em]">{@render icon('ne', 'h-3.5 w-3.5')} UNESA</p>
                        </div>

                        <!-- right: details -->
                        <div class="mt-2 flex flex-col justify-center gap-3 sm:mt-0">
                            <div class="{pixel} flex items-center justify-between gap-2.5 rounded-[10px] border-[3px] border-black bg-[#f5c518] px-3 py-2 shadow-[5px_5px_0_#000]">
                                <div><small class="block text-[10px] tracking-[.14em] opacity-80">MASTER</small><strong class="block text-xl font-silk font-normal tracking-[.12em]">BUDI</strong></div>
                                {@render icon('ne', 'h-5 w-5')}
                            </div>
                            <div class="{pixel} flex items-center gap-2.5 rounded-[10px] border-[3px] border-black bg-white px-3 py-2 shadow-[5px_5px_0_#000]">
                                <span class="grid h-8.5 w-8.5 shrink-0 place-items-center rounded-lg border-2 border-black bg-pale-purple">{@render icon('people', 'h-[18px] w-[18px]')}</span>
                                <div><small class="block text-[10px] tracking-[.14em] opacity-80">CATEGORY</small><strong class="block text-[15px] font-silk font-normal tracking-[.12em]">TECHNOLOGY</strong></div>
                            </div>
                            <div class="{pixel} flex items-center gap-2.5 rounded-[10px] border-[3px] border-black bg-white px-3 py-2.5 shadow-[5px_5px_0_#000]">
                                <span class="grid h-8.5 w-8.5 shrink-0 place-items-center rounded-lg border-2 border-black bg-[#2fc7b8]">{@render icon('db', 'h-[18px] w-[18px]')}</span>
                                <strong class="text-[15px] font-silk font-normal tracking-[.12em]">1 CREDIT</strong>
                            </div>
                            <div class="{pixel} flex items-center justify-between rounded-[10px] border-[3px] border-black bg-[#e8265f] px-3 py-3.5 font-silk text-base tracking-[.14em] text-white shadow-[5px_5px_0_#000]">
                                REKRUT {@render icon('ne', 'h-5 w-5')}
                            </div>
                        </div>
                    </div>

                    <!-- rating sticker -->
                    <div class="absolute bottom-2 left-0 z-10 w-40 -rotate-5 border-[3px] border-black bg-[#ffe477] px-4 py-3 text-center shadow-[6px_6px_0_#000] sm:w-44">
                        {@render icon('star', 'mx-auto h-6 w-6 fill-white')}
                        <b class="block font-mono text-2xl leading-tight">4.9/5</b>
                        <span class="font-mono text-[13px]">rating mentor</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================ CATEGORIES ========================== -->
    <section class="pb-20 pt-6 lg:pb-24">
        <div class={wrap}>
            <div class="mb-8 flex flex-col gap-4 lg:mb-11 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class={eyebrow}>Skill apa yang kamu punya?</div>
                    <h2 class={h2}>Enam kategori,<br />satu tempat belajar.</h2>
                </div>
                <p class="max-w-108 pb-2 text-lg lg:text-xl">Dari bimbel tugas sampai desain poster. Pilih kategori, lihat siapa yang mengajar, lalu ajukan sesi.</p>
            </div>

            <div class="relative grid grid-cols-2 gap-x-4 gap-y-9 border-[3px] border-black bg-white py-9 pl-11 pr-5 shadow-[7px_7px_0_#000] sm:grid-cols-3 lg:grid-cols-6 lg:pl-[60px] lg:pr-10">
                <div aria-hidden="true" class="absolute inset-y-0 left-0 w-[22px] border-r-[3px] border-black bg-neon-yellow"></div>
                {#each categories as cat}
                    <div class="flex flex-col items-center gap-1.5 text-center">
                        <div class="mb-2 grid h-24 w-24 place-items-center rounded-full border-[3px] border-black shadow-[4px_4px_0_#000] lg:h-26 lg:w-26 {cat.bg}">
                            <svg viewBox="0 0 64 64" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-16 w-16 lg:h-[70px] lg:w-[70px]" aria-hidden="true">{@html catIcons[cat.key]}</svg>
                        </div>
                        <b class="text-xl leading-tight">{cat.name}</b>
                        <small class="max-w-37.5 text-sm leading-snug text-black/75">{cat.hint}</small>
                    </div>
                {/each}
                <div aria-hidden="true" class="absolute right-3.5 top-1/2 hidden -translate-y-1/2 flex-col gap-2.5 lg:flex">
                    <i class="block h-[15px] w-[15px] border-2 border-black bg-neon-yellow"></i>
                    <i class="block h-[15px] w-[15px] border-2 border-black bg-laser-pink"></i>
                    <i class="block h-[15px] w-[15px] border-2 border-black bg-[#4ade80]"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================== PROBLEM =========================== -->
    <section class="pb-24">
        <div class={wrap}>
            <h2 class="{h2} mb-12 lg:mb-14">Freelance mahal.<br />Minta tolong gratis, nggak enak.</h2>

            <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
                <div class="{box} bg-off-white p-8 lg:-rotate-[.8deg] lg:px-10 lg:pb-10 lg:pt-9">
                    <h3 class="mb-5 flex items-center gap-3.5 font-anton text-5xl uppercase leading-none">
                        {@render icon('x', 'h-11 w-11 text-laser-pink')} Tanpa LetSo
                    </h3>
                    <ul class="grid gap-[18px]">
                        {#each without as line}
                            <li class="flex gap-4 text-lg leading-snug sm:text-xl">
                                <span class="mt-px grid h-8.5 w-8.5 shrink-0 place-items-center rounded-full border-[3px] border-black bg-white">{@render icon('x', 'h-4 w-4')}</span>
                                {line}
                            </li>
                        {/each}
                    </ul>
                </div>

                <div class="{box} bg-neon-yellow p-8 lg:rotate-[.8deg] lg:px-10 lg:pb-10 lg:pt-9">
                    <h3 class="mb-5 flex items-center gap-3.5 font-anton text-5xl uppercase leading-none">
                        {@render icon('check', 'h-11 w-11')} Dengan LetSo
                    </h3>
                    <ul class="grid gap-[18px]">
                        {#each withLetso as line}
                            <li class="flex gap-4 text-lg leading-snug sm:text-xl">
                                <span class="mt-px grid h-8.5 w-8.5 shrink-0 place-items-center rounded-full border-[3px] border-black bg-white">{@render icon('check', 'h-4 w-4')}</span>
                                {line}
                            </li>
                        {/each}
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================== STEPS ============================= -->
    <section id="cara-kerja" class="scroll-mt-4 pb-24 lg:pb-28">
        <div class={wrap}>
            <div class="mb-8 lg:mb-1.5">
                <div class={eyebrow}>Cara kerja</div>
                <h2 class={h2}>Empat langkah,<br />dari login sampai barter.</h2>
            </div>

            <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:items-start">
                {#each steps as step}
                    <li class="{box} relative p-6 lg:min-h-[296px] {step.cls}">
                        {#if step.google}
                            <span class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-full border-[3px] border-black bg-white">{@render googleG('h-6 w-6')}</span>
                        {/if}
                        <div class="mb-4 font-anton text-[118px] leading-[.8]">{step.n}</div>
                        <h3 class="mb-2.5 text-[27px] font-bold leading-[1.15]">{step.title}</h3>
                        <p class="text-[17px] leading-snug">{step.text}</p>
                    </li>
                {/each}
            </ol>
        </div>
    </section>

    <!-- ============================== CREDIT ============================ -->
    <section class="border-y-4 border-black bg-electric-cyan py-20 lg:pb-24 lg:pt-[90px]">
        <div class={wrap}>
            <h2 class="mb-10 font-anton text-[clamp(3.5rem,11vw,10rem)] uppercase leading-[.86] sm:whitespace-nowrap lg:mb-14">
                1 jam<span class="mx-[.12em] text-white [text-shadow:6px_6px_0_#000]">=</span>1 kredit
            </h2>

            <div class="grid items-center gap-12 lg:grid-cols-[560px_1fr] lg:gap-[70px]">
                <div>
                    <p class="mb-8 max-w-130 text-xl sm:text-[23px]">Mengajar menambah kredit, belajar mengurangi kredit. Semua tercatat di riwayat, jadi saldo kamu tidak bisa diubah sembarangan.</p>

                    <ol class="flex flex-wrap items-center gap-y-3 font-mono text-[13px] font-bold">
                        {#each flow as step, i}
                            <li class="whitespace-nowrap border-[3px] border-black px-3 py-2 shadow-[4px_4px_0_#000] {i === flow.length - 1 ? 'bg-laser-pink text-off-white' : 'bg-off-white'}">{step}</li>
                            {#if i < flow.length - 1}
                                <li aria-hidden="true" class="relative h-[3px] w-5.5 shrink-0 bg-black after:absolute after:-right-px after:-top-[5px] after:border-y-[6.5px] after:border-l-[9px] after:border-y-transparent after:border-l-black after:content-['']"></li>
                            {/if}
                        {/each}
                    </ol>
                </div>

                                <div class="{box} overflow-hidden bg-off-white">

                    <div class="flex items-baseline justify-between border-b-[3px] border-black px-6 pb-3.5 pt-6 sm:px-8">

                        <h3 class="font-anton text-4xl uppercase leading-none sm:text-[44px]">Riwayat Transaksi</h3>

                        <span class="font-mono text-[13px] font-bold">{ledger.length} AKTIVITAS</span>

                    </div>



                    {#each ledger as row}

                        <div class="grid grid-cols-[52px_1fr_auto] items-center gap-4 border-b-2 border-black px-6 py-4 last:border-b-0 sm:px-8">

                            <span class="grid h-13 w-13 shrink-0 place-items-center rounded-xl border-[3px] border-black {txnStyles[row.type].badge}">{@render icon(txnStyles[row.type].icon, 'h-6 w-6')}</span>



                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <b class="text-[18px] leading-tight sm:text-[19px]">{row.title}</b>

                                    <span class="border-2 border-black {txnStyles[row.type].badge} px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wide">{txnStyles[row.type].label}</span>

                                </div>

                                <p class="mt-1 truncate text-[14px] leading-snug text-black/70">{row.type === 'barter' ? `Barter dengan ${row.partner} · ditukar ${row.swapFor}` : `Bersama ${row.partner}`}</p>

                                <p class="mt-0.5 font-mono text-[12px] text-black/45">{row.date}</p>

                            </div>



                            <div class="flex flex-col items-end gap-2">

                                <span class="whitespace-nowrap border-[3px] border-black px-3 py-1 font-mono text-[13px] font-bold {row.delta > 0 ? 'bg-neon-yellow' : row.delta < 0 ? 'bg-laser-pink text-off-white' : 'bg-off-white'}">{row.delta > 0 ? `+${row.delta} credit` : row.delta < 0 ? `${row.delta} credit` : 'Tanpa credit'}</span>

                                <span class="whitespace-nowrap border-2 border-black bg-neon-yellow px-2.5 py-0.5 font-mono text-[11px] font-bold uppercase shadow-[2px_2px_0_#000]">Selesai</span>

                            </div>

                        </div>

                    {/each}

                </div>


            </div>
        </div>
    </section>

    <!-- ============================= FEATURES =========================== -->
    <section id="fitur" class="scroll-mt-4 pb-24 pt-24 lg:pt-28">
        <div class={wrap}>
            <div class="mb-8 flex flex-col gap-4 lg:mb-11 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class={eyebrow}>Fitur</div>
                    <h2 class={h2}>Barter yang aman,<br />komunitas yang seru.</h2>
                </div>
                <p class="max-w-108 pb-2 text-lg lg:text-xl">Bukan sekadar papan iklan skill. LetSo punya reputasi, kompetisi, dan pencatatan kredit yang jelas.</p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-[34px]">
                <!-- rating -->
                <article class="{box} flex min-h-[440px] flex-col bg-[#ffe477] p-7">
                    <div class="mb-5 flex items-center gap-3.5">
                        <span class={iconBox}>{@render icon('star', 'h-8 w-8')}</span>
                    </div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] uppercase leading-[.92]">Rating dan reputasi</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Setiap sesi dinilai dua arah. Rating dan skor reputasi tampil di profilmu, jadi mudah tahu siapa yang layak dipercaya.</p>
                    <div class="mt-auto flex items-center gap-1.5" aria-hidden="true">
                        {#each [1, 2, 3, 4] as s}
                            {@render icon('star', 'h-8.5 w-8.5 fill-white stroke-[1.8]')}
                        {/each}
                        {@render icon('star', 'h-8.5 w-8.5 fill-off-white stroke-[1.8] opacity-45')}
                        <span class="ml-2.5 border-[3px] border-black bg-white px-2.5 py-1 font-mono text-sm font-bold shadow-[3px_3px_0_#000]">Reputasi 100</span>
                    </div>
                </article>

                <!-- leaderboard -->
                <article id="leaderboard" class="{box} flex min-h-[440px] scroll-mt-4 flex-col bg-[#2fc7b8] p-7">
                    <div class="mb-5"><span class={iconBox}>{@render icon('trophy', 'h-8 w-8')}</span></div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] uppercase leading-[.92]">Leaderboard dan badge</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Makin banyak jam kontribusi, makin tinggi levelmu. Pantau kontributor terbanyak di kampus.</p>
                    <div class="mt-auto flex h-[92px] items-end gap-2.5 font-anton text-[34px] leading-none" aria-hidden="true">
                        <div class="grid h-[66px] flex-1 place-items-start justify-center border-[3px] border-black bg-laser-pink pt-1.5 text-off-white shadow-[4px_4px_0_#000]">2</div>
                        <div class="grid h-[92px] flex-1 place-items-start justify-center border-[3px] border-black bg-neon-yellow pt-1.5 shadow-[4px_4px_0_#000]">1</div>
                        <div class="grid h-[50px] flex-1 place-items-start justify-center border-[3px] border-black bg-electric-cyan pt-1.5 shadow-[4px_4px_0_#000]">3</div>
                    </div>
                </article>

                <!-- two-way confirmation -->
                <article class="{box} flex min-h-[440px] flex-col bg-laser-pink p-7 text-off-white">
                    <div class="mb-5 text-black"><span class={iconBox}>{@render icon('shield', 'h-8 w-8')}</span></div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] text-black uppercase leading-[.92]">Konfirmasi dua arah</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Kredit tidak berpindah sebelum pengajar dan pelajar sama-sama bilang sesi sudah selesai.</p>
                    <div class="mt-auto flex items-center gap-3" aria-hidden="true">
                        <div class="flex flex-col items-center gap-1.5 font-mono text-xs font-bold">
                            <span class="{avatar} h-13 w-13 text-2xl">B</span>Pengajar
                        </div>
                        <span class="relative -top-2.5 h-[3px] flex-1 bg-white"></span>
                        <span class="relative -top-2.5 grid h-10.5 w-10.5 place-items-center rounded-full border-[3px] border-black bg-neon-yellow text-black">{@render icon('check', 'h-[22px] w-[22px]')}</span>
                        <span class="relative -top-2.5 h-[3px] flex-1 bg-white"></span>
                        <div class="flex flex-col items-center gap-1.5 font-mono text-xs font-bold">
                            <span class="grid h-13 w-13 place-items-center rounded-full border-2 border-black bg-pale-purple text-2xl text-black">A</span>Pelajar
                        </div>
                    </div>
                </article>

                <!-- campus verification -->
                <article class="{box} flex min-h-[440px] flex-col bg-neon-yellow p-7">
                    <div class="mb-5"><span class={iconBox}>{@render icon('shield', 'h-8 w-8')}</span></div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] uppercase leading-[.92]">Verifikasi kampus</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Hanya mahasiswa asli yang bisa masuk. Login lewat Google dengan email kampus.</p>
                    <div class="mt-auto border-[3px] border-black bg-white px-4 py-3.5 font-mono text-[17px] font-bold shadow-[5px_5px_0_#000] sm:text-[19px]">
                        nim<em class="bg-laser-pink px-1 not-italic text-off-white">@mhs.unesa.ac.id</em>
                    </div>
                </article>

                <!-- chat: discussion and scheduling happen on WhatsApp -->
                <article class="{box} flex min-h-[440px] flex-col bg-electric-cyan p-7">
                    <div class="mb-5"><span class={iconBox}>{@render whatsapp('h-8 w-8 text-[#25d366]')}</span></div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] uppercase leading-[.92]">Chat dan jadwal</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Diskusi dan sepakati jadwal langsung lewat WhatsApp. Pilih online atau offline di aplikasi chat yang sudah kamu pakai tiap hari.</p>

                    <!-- WhatsApp-style conversation -->
                    <div class="relative mt-auto pb-3" aria-hidden="true">
                        <div class="border-[3px] border-black bg-[#efeae2] shadow-[5px_5px_0_#000]">
                            <div class="flex items-center gap-2.5 border-b-[3px] border-black bg-[#25d366] px-2.5 py-1.5">
                                <span class="{avatar} h-8 w-8 text-sm font-bold">B</span>
                                <span class="leading-none">
                                    <b class="block text-[15px]">Budi</b>
                                    <span class="font-mono text-[11px]">online</span>
                                </span>
                                <span class="ml-auto border-2 border-black bg-white px-2 py-0.5 font-mono text-[11px] font-bold">WhatsApp</span>
                            </div>

                            <div class="grid gap-2 px-2.5 pb-4 pt-3 text-[13.5px] leading-tight">
                                <div class="max-w-[88%] justify-self-end rounded-xl rounded-tr-sm border-2 border-black bg-[#d9fdd3] px-2.5 py-1.5 shadow-[3px_3px_0_#000]">
                                    Kak, besok jam 4 sore bisa?
                                    <span class="ml-1.5 whitespace-nowrap font-mono text-[10px] text-black/60">09.12 <b class="text-[#34b7f1]">✓✓</b></span>
                                </div>
                                <div class="max-w-[88%] justify-self-start rounded-xl rounded-tl-sm border-2 border-black bg-white px-2.5 py-1.5 shadow-[3px_3px_0_#000]">
                                    Bisa! Zoom atau offline?
                                    <span class="ml-1.5 whitespace-nowrap font-mono text-[10px] text-black/60">09.13</span>
                                </div>
                                <div class="max-w-[88%] justify-self-end rounded-xl rounded-tr-sm border-2 border-black bg-[#d9fdd3] px-2.5 py-1.5 shadow-[3px_3px_0_#000]">
                                    Zoom aja, kirim linknya ya.
                                    <span class="ml-1.5 whitespace-nowrap font-mono text-[10px] text-black/60">09.14 <b class="text-[#34b7f1]">✓✓</b></span>
                                </div>
                            </div>
                        </div>
                        <!-- agreed schedule, pinned like a sticker -->
                        <span class="absolute bottom-0 left-3 z-10 -rotate-2 border-2 border-black bg-neon-yellow px-2 py-0.5 font-mono text-[11px] font-bold shadow-[2px_2px_0_#000]">Besok 16.00 · Zoom</span>
                    </div>
                </article>

                <!-- timeline -->
                <article class="{box} flex min-h-[440px] flex-col bg-[#ffa174] p-7">
                    <div class="mb-5"><span class={iconBox}>{@render icon('people', 'h-8 w-8')}</span></div>
                    <h3 class="mb-3 font-anton text-[clamp(2.4rem,4vw,3.5rem)] uppercase leading-[.92]">Timeline komunitas</h3>
                    <p class="mb-5 max-w-85 text-lg leading-snug">Bagikan yang sedang kamu pelajari dan temukan teman barter.</p>
                    <div class="mt-auto border-[3px] border-black bg-off-white shadow-[5px_5px_0_#000]" aria-hidden="true">
                        <div class="flex items-center justify-between border-b-[3px] border-black bg-[#2fc7b8] px-2.5 py-1 font-mono text-[11px] font-bold">
                            <span>Alya's Post</span>
                            <span class="flex gap-1.5">
                                <i class="block h-3 w-3 rounded-full border-2 border-black bg-laser-pink"></i>
                                <i class="block h-3 w-3 rounded-full border-2 border-black bg-[#ffe477]"></i>
                                <i class="block h-3 w-3 rounded-full border-2 border-black bg-[#67d986]"></i>
                            </span>
                        </div>
                        <p class="px-3.5 py-3 text-center text-[14.5px] leading-snug">Lagi belajar Figma auto layout. Ada yang mau tukar dengan Laravel?</p>
                        <div class="flex justify-end gap-2 px-2.5 pb-2.5 font-mono text-[11px] font-bold">
                            <span class="border-2 border-black bg-white px-2.5 py-0.5">♥ 3 Like</span>
                            <span class="border-2 border-black bg-white px-2.5 py-0.5">● 2 Comment</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- ================================ SDG ============================= -->
    <div class="bg-black py-8 text-white">
        <div class="{wrap} flex flex-col items-start gap-6 lg:flex-row lg:items-center lg:justify-between">
            <p class="max-w-85 text-[19px] leading-snug">LetSo dibangun untuk mendukung tiga tujuan pembangunan berkelanjutan.</p>
            <ul class="flex flex-wrap gap-5">
                {#each sdgs as g}
                    <li class="flex items-center gap-3.5 border-[3px] border-white py-2.5 pl-2.5 pr-4.5">
                        <b class="grid h-13.5 w-13.5 place-items-center border-[3px] border-black font-anton text-[26px] font-normal leading-none text-black {g.bg}">{g.n}</b>
                        <span class="max-w-32 text-[15px] leading-tight">{g.text}</span>
                    </li>
                {/each}
            </ul>
        </div>
    </div>

    <!-- ================================ FAQ ============================= -->
    <section class="py-24 lg:py-28">
        <div class={wrap}>
            <div class="mb-10 lg:mb-11">
                <div class={eyebrow}>Pertanyaan umum</div>
                <h2 class={h2}>Masih ragu? Baca ini dulu.</h2>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:gap-[34px]">
                {#each faq as item}
                    <div class="{box} bg-off-white px-7 pb-7 pt-6">
                        <h3 class="mb-2.5 flex items-center gap-3.5 text-2xl font-bold leading-tight">
                            <i class="grid h-8.5 w-8.5 shrink-0 place-items-center border-[3px] border-black bg-neon-yellow font-mono text-lg font-bold not-italic">?</i>
                            {item.q}
                        </h3>
                        <p class="pl-12 text-[17px] leading-normal">{item.a}</p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- ============================ FINAL CTA =========================== -->
    <section class="pb-24 lg:pb-28">
        <div class={wrap}>
            <div class="relative overflow-hidden border-4 border-black bg-laser-pink px-7 py-14 text-off-white shadow-[14px_14px_0_#000] sm:px-12 lg:min-h-[500px] lg:p-[70px]">
                <div aria-hidden="true" class="absolute bottom-0 right-0 hidden h-[330px] w-[520px] bg-[#ffa174] md:block {stairs}"></div>

                <div aria-hidden="true" class="absolute right-16 top-11 z-20 hidden h-[190px] w-[190px] rotate-[8deg] place-items-center rounded-full border-4 border-black bg-neon-yellow text-center font-anton uppercase leading-[.9] text-black shadow-[8px_8px_0_#000] lg:grid">
                    <div><b class="block text-[76px] font-normal">+1</b><span class="text-3xl">kredit awal</span></div>
                </div>

                <div aria-hidden="true" class="absolute bottom-14 right-[250px] z-20 hidden w-[300px] -rotate-6 items-center gap-3 border-[3px] border-black bg-off-white px-4 py-3 text-[15px] leading-snug text-black shadow-[6px_6px_0_#000] lg:flex">
                    <span class="{avatar} h-10 w-10 text-xl">B</span>
                    <span><b>Budi</b> siap ngajar PHP. 1 kredit, 1 jam.</span>
                </div>

                <h2 class="relative z-10 mb-8 max-w-250 font-anton text-[clamp(2.6rem,7.2vw,6.5rem)] uppercase leading-[.92] [text-shadow:5px_5px_0_#000]">
                    Satu jam dari kamu,<br />satu jam buat kamu.
                </h2>
                <p class="relative z-10 mb-9 max-w-140 text-xl sm:text-[22px]">Masuk dengan email kampus, pasang skill pertamamu, dan rekrut mentor hari ini.</p>

                <div class="relative z-10">
                    {@render googleBtn('bg-white text-black gap-3.5 py-3.5 pl-4 pr-7 text-lg shadow-[8px_8px_0_#000] sm:pr-8.5 sm:text-[23px]', 'h-9 w-9 bg-neon-yellow sm:h-11 sm:w-11')}
                    <p class="mt-6 font-mono text-sm">Khusus mahasiswa UNESA · @mhs.unesa.ac.id</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================== FOOTER ============================ -->
    <footer class="bg-black py-14 text-white">
        <div class={wrap}>
            <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
                <div>
                    <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 flex transition-transform hover:-translate-y-1 sm:h-21 sm:w-54">
                        <img src={symbol} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
                    </a>
                    <p class="mt-5 max-w-105 text-lg text-white/85">Barter skill antar mahasiswa. Bayarnya pakai waktu, bukan uang.</p>
                </div>
                <nav aria-label="Footer" class="flex flex-wrap gap-x-8 gap-y-3 pt-3 font-mono text-[15px]">
                    {#each navItems as item}
                        <button type="button" onclick={() => goTo(item.id)} class="hover:underline {focus} focus-visible:outline-white">{item.label}</button>
                    {/each}
                    <button type="button" onclick={startNow} class="hover:underline {focus} focus-visible:outline-white">Masuk</button>
                </nav>
            </div>

            <div class="mt-11 flex flex-col gap-2 border-t-2 border-white/20 pt-5 font-mono text-[13px] text-white/60 sm:flex-row sm:justify-between">
                <span>© 2026 LetSo. Dibuat untuk mahasiswa UNESA.</span>
            </div>
        </div>
    </footer>
</main>