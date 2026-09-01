<script>
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";

    const podium = [
        { name: "Kastama", score: "4.9/5", reputation: "80/100", transactions: 10, color: "bg-laser-pink", height: "h-48", order: "order-1" },
        { name: "Kastama", score: "4.9/5", reputation: "80/100", transactions: 10, color: "bg-neon-yellow", height: "h-76", order: "order-2" },
        { name: "Kastama", score: "4.9/5", reputation: "80/100", transactions: 10, color: "bg-electric-cyan", height: "h-32", order: "order-3" }
    ];

    const users = [
        { name: "ReinZal Kinas Pratama", score: "4.9/5 Rating  |  80/100 Reputation  |  10 Transaction", color: "bg-neon-yellow" },
        { name: "Ibna Clevaro Syarif", score: "4.8/5 Rating  |  76/100 Reputation  |  8 Transaction", color: "bg-laser-pink" },
        { name: "Alya Putri", score: "4.7/5 Rating  |  72/100 Reputation  |  7 Transaction", color: "bg-electric-cyan" }
    ];
</script>

<main class="min-h-screen overflow-hidden bg-[#ffa174] px-5 py-7 sm:px-10 lg:px-18">
    <header class="dashboard-enter mx-auto grid max-w-255 grid-cols-[1fr_auto_1fr] items-center gap-4 pt-0 sm:pt-1">
        <a href="/#/" aria-label="Faiz home" class="h-10 w-24 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] sm:h-12 sm:w-32"></a>
        <Navbar />
        <div class="justify-self-end"><ProfileDropdown /></div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-10 max-w-255 text-center">
        <h1 class="font-mono text-4xl font-bold tracking-[0.16em] sm:text-5xl">LEADERBOARDS</h1>
        <p class="font-mono text-lg tracking-[0.2em] sm:text-xl">(by Rating)</p>

        <div class="mx-auto mt-5 flex max-w-175 items-end justify-center gap-2 sm:gap-4">
            {#each podium as person}
                <div class="{person.order} flex w-1/3 max-w-44 flex-col items-center">
                    <div class="mb-2 flex h-14 w-14 items-center justify-center rounded-full border-2 border-pitch-black bg-off-white font-mono text-xs sm:h-16 sm:w-16">●</div>
                    <h2 class="font-mono text-xs font-bold uppercase sm:text-base">{person.name}</h2>
                    <p class="font-mono text-xs font-bold sm:text-sm">{person.score} <span class="text-[#ffe477]">★</span></p>
                    <div class="{person.height} mt-1 flex w-full flex-col items-center justify-between border-2 border-pitch-black {person.color} px-2 py-4 shadow-[7px_7px_0_#000] sm:py-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-b from-gray-300 to-gray-500 font-archivo text-2xl font-bold text-white">2</span>
                        <div class="font-mono text-center font-bold">
                            <p class="text-base sm:text-2xl">{person.reputation}</p>
                            <p class="text-[10px] sm:text-sm">Reputation</p>
                            <p class="mt-4 text-base sm:text-2xl">{person.transactions}</p>
                            <p class="text-[10px] sm:text-sm">Transaction</p>
                        </div>
                    </div>
                </div>
            {/each}
        </div>
    </section>

    <section class="dashboard-enter dashboard-enter-delay-2 mx-auto mt-10 max-w-255 space-y-7 pb-10">
        {#each users as user, index}
            <article class="flex min-h-24 items-center gap-4 border-2 border-pitch-black px-5 py-4 shadow-[7px_7px_0_#000] {user.color} sm:gap-6 sm:px-10">
                <div class="h-16 w-16 shrink-0 border-2 border-pitch-black bg-off-white shadow-[4px_4px_0_#000] sm:h-20 sm:w-20"></div>
                <div class="min-w-0 flex-1 font-mono">
                    <h2 class="truncate text-sm font-bold tracking-widest sm:text-lg">{user.name}</h2>
                    <div class="mt-1 border-t-2 border-pitch-black pt-2 text-[10px] font-bold sm:text-sm">★{user.score}</div>
                </div>
                <span class="hidden font-mono text-2xl font-bold sm:block">0{index + 1}</span>
            </article>
        {/each}
    </section>
</main>