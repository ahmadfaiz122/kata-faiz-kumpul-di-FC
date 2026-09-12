<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import CategoryBar from "../lib/CategoryBar.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import SkillCard from "../lib/SkillCard.svelte";
    import logo from "../assets/logo.png";

    editPage("Swapp");

    let searchQuery = "";

    function submitSearch() {
        searchQuery = searchQuery.trim();
    }

    const skillOffers = [
        { duration: "2 HOUR", mentor: "Reinzal", skill: "Svelte Anjay", category: "Design", university: "University of Surabaya", credit: 2 },
        { duration: "1 HOUR", mentor: "Alya", skill: "Brand Strategy", category: "Business", university: "University of Surabaya", credit: 1 }
    ];
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 flex max-w-320 flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <CategoryBar />
        <form class="search-bar relative flex lg:w-100 items-center rounded-full border-2 border-pitch-black bg-white shadow-[6px_6px_0_#000]" onsubmit={(event) => { event.preventDefault(); submitSearch(); }}>
            <label for="skill-search" class="sr-only">Cari skill</label>
            <input id="skill-search" bind:value={searchQuery} type="search" placeholder="Lagi penasaran sama apa nih?" class="relative z-10 min-w-0 flex-1 bg-transparent px-8 py-4 font-archivo text-sm text-pitch-black outline-none placeholder:text-pitch-black" />
            <button type="submit" aria-label="Cari" class="relative z-10 px-6 py-4 text-xl text-pitch-black">⌕</button>
        </form>
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
