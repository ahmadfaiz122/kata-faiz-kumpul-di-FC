<script>
    import CategoryBar from "../lib/CategoryBar.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import SkillCard from "../lib/SkillCard.svelte";

    const skillOffers = [
        { duration: "2 HOUR", mentor: "Reinzal", skill: "Svelte Anjay", category: "Design", university: "University of Surabaya", credit: 2 },
        { duration: "1 HOUR", mentor: "Alya", skill: "Brand Strategy", category: "Business", university: "Institut Teknologi Sepuluh Nopember", credit: 1 }
    ];
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-11 w-28 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"></a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 flex max-w-320 flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <CategoryBar />
        <div class="flex min-h-20 items-center justify-center rounded-full border-2 border-pitch-black bg-laser-pink px-8 text-center shadow-[6px_6px_0_#000] lg:w-80">
            <p class="font-mono text-sm text-off-white sm:text-base">Lagi penasaran sama apa?</p>
        </div>
    </section>

    <section class="dashboard-enter dashboard-enter-delay-2 mx-auto mt-10 max-w-320" aria-labelledby="offers-title">
        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <p class="font-mono text-sm uppercase tracking-wide text-laser-pink">Temukan teman belajar</p>
                <h1 id="offers-title" class="font-anton text-4xl uppercase sm:text-5xl">Skill terbaru</h1>
            </div>
            <span class="hidden font-mono text-sm sm:block">01 / 06</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {#each skillOffers as offer, index}
                <div class:dashboard-enter={index === 0} class:dashboard-enter-delay-3={index === 1}>
                    <SkillCard {...offer} />
                </div>
            {/each}
        </div>

        <div class="flex justify-center py-16">
            <button type="button" class="button-lift border-2 border-pitch-black bg-off-white px-7 py-3 font-mono text-xs uppercase shadow-[4px_4px_0_#000]" style="--button-complement: #00d9ff">Load More</button>
        </div>
    </section>
</main>