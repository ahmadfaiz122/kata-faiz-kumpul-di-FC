<script>
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    // ------------------------------------------------------------------
    // Placeholder "backend data". Shape mirrors what /api/transactions +
    // the credit ledger would realistically return once this is wired up:
    //   mode        : "credit" | "skill"      -> how the session is paid
    //   direction   : "teaching" | "learning" -> only meaningful when mode === "credit"
    //   status      : "pending" | "active" | "completed" | "rejected" | "cancelled" | "expired"
    // Swap this array for a real fetch later; the markup below only reads
    // from `transactions`, so nothing else needs to change.
    // ------------------------------------------------------------------
    let transactions = $state([
        {
            id: 101,
            mode: "credit",
            direction: "learning",
            skill_name: "Public Speaking",
            counterpart_name: "Dimas Ari",
            hour: 1,
            status: "pending",
            starts_at: "2026-09-24T15:00:00",
            created_at: "2026-09-20T09:41:00",
        },
        {
            id: 102,
            mode: "skill",
            skill_name: "UI/UX di Figma",
            counterpart_name: "Alya Putri",
            counterpart_skill_name: "Laravel Dasar",
            hour: 2,
            status: "active",
            starts_at: "2026-09-23T13:00:00",
            created_at: "2026-09-19T11:05:00",
        },
        {
            id: 96,
            mode: "credit",
            direction: "teaching",
            skill_name: "PHP Dasar",
            counterpart_name: "Budi Santoso",
            hour: 1,
            status: "completed",
            starts_at: "2026-09-16T16:00:00",
            created_at: "2026-09-12T08:20:00",
        },
        {
            id: 94,
            mode: "credit",
            direction: "learning",
            skill_name: "Bahasa Inggris (TOEFL)",
            counterpart_name: "Sarah Amelia",
            hour: 1,
            status: "completed",
            starts_at: "2026-09-14T10:00:00",
            created_at: "2026-09-10T19:12:00",
        },
        {
            id: 91,
            mode: "skill",
            skill_name: "Copywriting",
            counterpart_name: "Reza Pratama",
            counterpart_skill_name: "Editing Video",
            hour: 1,
            status: "completed",
            starts_at: "2026-09-11T09:00:00",
            created_at: "2026-09-08T14:30:00",
        },
        {
            id: 88,
            mode: "credit",
            direction: "teaching",
            skill_name: "Statistika Dasar",
            counterpart_name: "Nadia Kusuma",
            hour: 1,
            status: "rejected",
            starts_at: "2026-09-09T15:00:00",
            created_at: "2026-09-07T10:02:00",
        },
        {
            id: 85,
            mode: "credit",
            direction: "learning",
            skill_name: "Fotografi Produk",
            counterpart_name: "Fajar Nugraha",
            hour: 1,
            status: "cancelled",
            starts_at: "2026-09-05T13:00:00",
            created_at: "2026-09-02T16:45:00",
        },
        {
            id: 80,
            mode: "skill",
            skill_name: "Editing Video",
            counterpart_name: "Clara Ivena",
            counterpart_skill_name: "Desain Poster",
            hour: 1,
            status: "expired",
            starts_at: "2026-08-30T10:00:00",
            created_at: "2026-08-27T09:00:00",
        },
    ]);

    const ONGOING_STATUSES = ["pending", "scheduled", "active"];

    let ongoing = $derived(transactions.filter((t) => ONGOING_STATUSES.includes(t.status)));
    let history = $derived(
        transactions
            .filter((t) => !ONGOING_STATUSES.includes(t.status))
            .sort((a, b) => new Date(b.starts_at).getTime() - new Date(a.starts_at).getTime())
    );

    /** @param {string} iso */
    function formatDateTime(iso) {
        return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium", timeStyle: "short" }).format(new Date(iso));
    }

    /** What kind of session this is — drives the label shown on each card. */
    /** @param {{mode?: string, direction?: string}} t */
    function typeInfo(t) {
        if (t.mode === "skill") return { label: "Barter Skill", cls: "bg-pale-purple text-off-white" };
        return t.direction === "teaching"
            ? { label: "Mengajar", cls: "bg-[#2fc7b8]" }
            : { label: "Belajar", cls: "bg-[#ffa174]" };
    }

    /** Whether credit moves, and in which direction: +, -, or neither (barter). */
    /** @param {{mode?: string, direction?: string, hour?: number}} t */
    function creditInfo(t) {
        if (t.mode === "skill") return { text: "Tanpa credit", cls: "bg-off-white border-2 border-pitch-black" };
        if (t.direction === "teaching") return { text: `+${t.hour} credit`, cls: "bg-cyber-lime" };
        return { text: `-${t.hour} credit`, cls: "bg-laser-pink text-off-white" };
    }

    /** @type {Record<string, {label: string, cls: string}>} */
    const STATUS_MAP = {
        pending: { label: "Menunggu konfirmasi", cls: "bg-neon-yellow" },
        scheduled: { label: "Terjadwal", cls: "bg-[#8bd5ff]" },
        active: { label: "Berlangsung", cls: "bg-electric-cyan" },
        completed: { label: "Selesai", cls: "bg-cyber-lime" },
        rejected: { label: "Ditolak", cls: "bg-laser-pink text-off-white" },
        cancelled: { label: "Dibatalkan", cls: "bg-off-white border-2 border-pitch-black" },
        expired: { label: "Kedaluwarsa", cls: "bg-off-white border-2 border-pitch-black" },
    };
    /** @param {string} status */
    function statusInfo(status) {
        return STATUS_MAP[status] ?? { label: status, cls: "bg-off-white border-2 border-pitch-black" };
    }
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <div class="pointer-events-none absolute -left-20 -top-16 h-44 w-44 rounded-full border-2 border-pitch-black bg-cyber-lime sm:h-60 sm:w-60 lg:h-72 lg:w-72"></div>
    <div class="pointer-events-none absolute -right-14 top-32 hidden h-20 w-20 rotate-12 border-2 border-pitch-black bg-laser-pink sm:block sm:h-28 sm:w-28"></div>
    <div class="pointer-events-none absolute -bottom-16 -right-12 h-40 w-40 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-52 sm:w-52"></div>
    <div class="pointer-events-none absolute bottom-24 left-4 hidden h-14 w-14 -rotate-6 border-2 border-pitch-black bg-electric-cyan lg:block"></div>

    <header class="dashboard-enter relative z-30 mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 max-w-240">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4 border-l-8 border-neon-yellow pl-5">
            <div>
                <p class="font-mono text-sm uppercase tracking-wide text-laser-pink">Riwayat aktivitas</p>
                <h1 class="font-anton text-5xl uppercase leading-none sm:text-6xl">Transaksi</h1>
                <p class="mt-4 max-w-2xl text-base leading-relaxed sm:text-lg">Semua sesi belajar-mengajar kamu, lengkap dengan waktu, jenis transaksi, dan status credit-nya.</p>
            </div>
            <a href="/#/messages" class="button-lift shrink-0 border-2 border-pitch-black bg-off-white px-4 py-2 font-mono text-xs font-bold uppercase shadow-[4px_4px_0_#000]" style="--button-complement: #ff006e">← Messages</a>
        </div>

        {#if ongoing.length > 0}
            <section class="mb-12" aria-labelledby="ongoing-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="ongoing-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Sedang berlangsung</h2>
                    <span class="border-2 border-pitch-black bg-neon-yellow px-2 py-1 font-mono text-[10px] font-bold uppercase shadow-[3px_3px_0_#000]">{ongoing.length} transaksi</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    {#each ongoing as t (t.id)}
                        {@const type = typeInfo(t)}
                        {@const credit = creditInfo(t)}
                        {@const status = statusInfo(t.status)}
                        <article class="transaction-card {status.cls}">
                            <div class="flex items-start justify-between gap-3">
                                <span class="border-2 border-pitch-black px-2 py-1 font-mono text-[10px] font-bold uppercase shadow-[2px_2px_0_#000] {type.cls}">{type.label}</span>
                                <span class="shrink-0 font-mono text-[10px] font-bold uppercase">{status.label}</span>
                            </div>
                            <h3 class="mt-3 font-anton text-2xl uppercase leading-tight">{t.skill_name}</h3>
                            <p class="mt-1 font-archivo text-sm">
                                {t.mode === "skill" ? "Barter dengan" : "Bersama"} <strong>{t.counterpart_name}</strong>
                                {#if t.mode === "skill" && t.counterpart_skill_name}
                                    <span class="block font-mono text-[10px] text-pitch-black/70">Ditukar dengan: {t.counterpart_skill_name}</span>
                                {/if}
                            </p>
                            <p class="mt-2 font-mono text-[10px] text-pitch-black/70">Jadwal: {formatDateTime(t.starts_at)}</p>
                            <div class="mt-4 flex items-center justify-between border-t-2 border-pitch-black pt-3">
                                <span class="border-2 border-pitch-black px-2 py-1 font-mono text-[11px] font-bold shadow-[2px_2px_0_#000] {credit.cls}">{credit.text}</span>
                                <span class="font-mono text-[9px] uppercase text-pitch-black/60">Ongoing</span>
                            </div>
                        </article>
                    {/each}
                </div>
            </section>
        {/if}

        <section aria-labelledby="history-title">
            <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                <h2 id="history-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Riwayat transaksi</h2>
                <span class="font-mono text-[10px] font-bold uppercase">{history.length} Transaksi</span>
            </div>

            {#if history.length === 0}
                <p class="border-2 border-pitch-black bg-off-white p-5 font-mono text-xs font-bold shadow-[6px_6px_0_#000]">Belum ada transaksi yang selesai.</p>
            {:else}
                <div class="grid gap-4">
                    {#each history as t (t.id)}
                        {@const type = typeInfo(t)}
                        {@const credit = creditInfo(t)}
                        {@const status = statusInfo(t.status)}
                        <article class="history-row">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-pitch-black font-mono text-lg font-bold shadow-[3px_3px_0_#000] {type.cls}">
                                {t.mode === "skill" ? "⇄" : t.direction === "teaching" ? "↑" : "↓"}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-archivo text-sm font-bold">{t.skill_name}</span>
                                    <span class="border-2 border-pitch-black px-1.5 py-0.5 font-mono text-[9px] font-bold uppercase {type.cls}">{type.label}</span>
                                </div>
                                <p class="mt-1 truncate font-mono text-[11px] text-pitch-black/70">
                                    {t.mode === "skill" ? "Barter dengan" : "Bersama"} {t.counterpart_name}
                                    {#if t.mode === "skill" && t.counterpart_skill_name} · ditukar {t.counterpart_skill_name}{/if}
                                </p>
                                <p class="mt-1 font-mono text-[10px] text-pitch-black/50">{formatDateTime(t.starts_at)}</p>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <span class="border-2 border-pitch-black px-2 py-1 font-mono text-[10px] font-bold shadow-[2px_2px_0_#000] {credit.cls}">{credit.text}</span>
                                <span class="border-2 border-pitch-black px-2 py-1 font-mono text-[9px] font-bold uppercase {status.cls}">{status.label}</span>
                            </div>
                        </article>
                    {/each}
                </div>
            {/if}
        </section>
    </section>
</main>

<style>
    .transaction-card {
        border: 2px solid #000;
        padding: 1.1rem;
        box-shadow: 6px 6px 0 #000;
    }

    .history-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 2px solid #000;
        background: #fffdf5;
        padding: 0.9rem 1.1rem;
        box-shadow: 4px 4px 0 #000;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .history-row:hover {
        transform: translateY(-2px);
        box-shadow: 6px 6px 0 #000;
    }

    .transaction-card {
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .transaction-card:hover {
        transform: translateY(-3px);
        box-shadow: 9px 9px 0 #000;
    }
</style>