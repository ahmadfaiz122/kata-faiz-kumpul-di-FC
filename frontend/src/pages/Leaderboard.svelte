<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import { onMount } from "svelte";

    editPage("Leaderboard");
    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let users = $state([]);
    let loading = $state(true);
    let error = $state("");

    onMount(async () => {
        try {
            const response = await fetch(`${backendUrl}/api/leaderboard`, { headers: { Accept: "application/json" } });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Leaderboard gagal dimuat.");
            users = result.data || [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Leaderboard gagal dimuat.";
        } finally {
            loading = false;
        }
    });

    let podium = $derived(users.slice(0, 3).map((person, index) => ({
        ...person,
        order: index === 0 ? "order-2" : index === 1 ? "order-1" : "order-3",
        height: index === 0 ? "h-80" : index === 1 ? "h-65" : "h-58",
        color: index === 0 ? "bg-neon-yellow" : index === 1 ? "bg-laser-pink" : "bg-electric-cyan",
    })));
</script>

<main class="min-h-screen overflow-hidden bg-[#ffa174] px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"><img src="src/assets/logo.webp" alt="Faiz Logo" class="h-full w-full scale-[1.2] object-contain" /></a>
        <Navbar />
        <div class="justify-self-end"><ProfileDropdown /></div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 max-w-320 text-center">
        <h1 class="font-mono text-4xl font-bold tracking-[0.16em] sm:text-5xl">LEADERBOARDS</h1>
        <p class="font-mono text-lg tracking-[0.2em] sm:text-xl">(Composite Score)</p>

        {#if loading}
            <p class="mt-10 font-mono text-sm">Memuat leaderboard...</p>
        {:else if error}
            <p class="mt-10 font-mono text-sm text-laser-pink">{error}</p>
        {:else if users.length === 0}
            <p class="mt-10 font-mono text-sm">Belum ada user dengan minimal 3 transaksi selesai.</p>
        {/if}
        <div class="mx-auto mt-5 flex max-w-175 items-end justify-center gap-2 sm:gap-4">
            {#each podium as person}
                <div class="{person.order} flex w-1/3 max-w-44 flex-col items-center">
                    <div class="mb-2 flex h-14 w-14 items-center justify-center rounded-full border-2 border-pitch-black bg-off-white font-mono text-xs sm:h-16 sm:w-16">●</div>
                    <h2 class="font-mono text-xs font-bold uppercase sm:text-base">{person.name}</h2>
                    <p class="font-mono text-xs font-bold sm:text-sm">{person.score} <span class="text-[#ffe477] text-xl">★</span></p>
                    <div class="{person.height} mt-1 flex w-full flex-col items-center justify-between border-2 border-pitch-black {person.color} px-2 py-4 shadow-[7px_7px_0_#000] sm:py-6">
                    {#if person.height === "h-80"}
                        <div class="relative z-10 w-[50px] h-[54px] p-1.5 flex items-center justify-center bg-[linear-gradient(155deg,#6e4a0e,#9c6b15_40%,#e8b84b_100%)] drop-shadow-[0_10px_22px_rgba(232,184,75,0.35)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
          <div class="w-full h-full flex items-center justify-center bg-[radial-gradient(120%_130%_at_32%_22%,#ffe9a8,#e8b84b_45%,#9c6b15_100%)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
            <span class=" font-black text-[#4a2e05] text-2xl [text-shadow:0_1px_0_rgba(255,255,255,0.35)]">1</span>
          </div>
        </div>
                    {:else if person.height === "h-65"}
                    <div class="relative z-10 w-[50px] h-[54px] p-1.5 flex items-center justify-center bg-[linear-gradient(155deg,#3a424b,#5b6673_40%,#b9c2cc_100%)] drop-shadow-[0_10px_22px_rgba(185,194,204,0.30)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
          <div class="w-full h-full flex items-center justify-center bg-[radial-gradient(120%_130%_at_32%_22%,#f6f8fa,#b9c2cc_45%,#5b6673_100%)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
            <span class=" font-black text-[#313a42] text-2xl [text-shadow:0_1px_0_rgba(255,255,255,0.35)]">2</span>
          </div>
        </div>
                    {:else}
                    <div class="relative z-10 w-[50px] h-[54px] p-1.5 flex items-center justify-center bg-[linear-gradient(155deg,#452409,#6b3410_40%,#b5651d_100%)] drop-shadow-[0_10px_22px_rgba(181,101,29,0.32)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
          <div class="w-full h-full flex items-center justify-center bg-[radial-gradient(120%_130%_at_32%_22%,#edb37e,#b5651d_45%,#6b3410_100%)] [clip-path:polygon(50%_0%,100%_22%,100%_63%,50%_100%,0_63%,0_22%)]">
            <span class="font-black text-[#3a1c08] text-2xl [text-shadow:0_1px_0_rgba(255,255,255,0.30)]">3</span>
          </div>
        </div>
                    {/if}
                        <div class="font-mono text-center font-bold">
                            <p class="text-base sm:text-2xl">{Math.round(person.reputation)}/100</p>
                            <p class="text-[10px] sm:text-sm">Reputation</p>
                            <p class="mt-4 text-base sm:text-2xl">{person.transactions}</p>
                            <p class="text-[10px] sm:text-sm">Transaction</p>
                        </div>
                    </div>
                </div>
            {/each}
        </div>
    </section>

    <section class="dashboard-enter dashboard-enter-delay-2 mx-auto mt-10 max-w-320 space-y-7 pb-10">
        {#each users.slice(3) as user, index}
            {@const rowColor = index % 3 === 0 ? "bg-neon-yellow" : index % 3 === 1 ? "bg-laser-pink" : "bg-electric-cyan"}
            <article class="flex min-h-24 items-center gap-4 border-2 border-pitch-black px-5 py-4 shadow-[7px_7px_0_#000] {rowColor} sm:gap-6 sm:px-10">
                <span class="font-mono text-2xl font-bold sm:block">{String(index + 4).padStart(2, "0")}</span>
                <div class="h-16 w-16 shrink-0 overflow-hidden border-2 border-pitch-black bg-off-white shadow-[4px_4px_0_#000] sm:h-20 sm:w-20">
                    {#if user.avatar}<img src={user.avatar} alt={user.name} class="h-full w-full object-cover" />{/if}
                </div>
                <div class="min-w-0 flex-1 font-mono">
                    <h2 class="truncate text-sm font-bold tracking-widest sm:text-lg">{user.name}</h2>
                    <div class="mt-1 border-t-2 border-pitch-black pt-2 text-[10px] font-bold sm:text-sm">Score {user.score} · Rating {user.rating}/5 · Reputasi {Math.round(user.reputation)}/100 · {user.transactions} transaksi</div>
                </div>
            </article>
        {/each}
    </section>
</main>
