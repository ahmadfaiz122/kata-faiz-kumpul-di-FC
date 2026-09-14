<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import CategoryBar from "../lib/CategoryBar.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import SkillCard from "../lib/SkillCard.svelte";
    import logo from "../assets/logo.png";
    import FilterDropdown from "../lib/FilterDropdown.svelte";

    editPage("Swapp");

    let searchQuery = "";

    function submitSearch() {
        searchQuery = searchQuery.trim();
    }
    let activeBar = $state("search");
    let filterOpen = $state(false)
    let filterTriggerEl = $state(null);
        function selectSearch() {

        activeBar = "search";

        filterOpen = false;

    }



    function selectFilter() {

        if (activeBar !== "filter") {

            activeBar = "filter";

            filterOpen = false;

        } else {

            filterOpen = !filterOpen;

        }

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
        <div class="relative flex items-center gap-3 lg:w-100">
                        

            <button

                type="button"

                onclick={selectSearch}

                aria-expanded={activeBar === "search"}

                aria-label="Cari"

                class="button-lift flex items-center overflow-hidden rounded-full border-2 border-pitch-black bg-white font-archivo text-sm text-off-white shadow-[6px_6px_0_#000] transition-[flex,padding] duration-300 ease-out {activeBar ===

                'search'

                    ? 'flex-1 justify-between px-8 py-4'

                    : 'h-14 w-14 flex-none justify-center p-0'}"

                style="--button-complement: #00d9ff"

            >

                {#if activeBar === "search"}

                    <input bind:value={searchQuery} class="mx-auto whitespace-nowrap relative z-10 min-w-0 flex-1 bg-transparent font-archivo text-sm text-pitch-black outline-none placeholder:text-pitch-black" placeholder="Lagi penasaran sama apa nih?" />

                {/if}

                <span aria-hidden="true" class="text-xl text-pitch-black">⌕</span>

            </button>

            <button

                type="button"

                bind:this={filterTriggerEl}

                onclick={selectFilter}

                aria-expanded={activeBar === "filter" && filterOpen}

                aria-label="Filter"

                class="button-lift flex items-center overflow-hidden rounded-full border-2 border-pitch-black bg-[#F2A672] font-archivo text-sm text-pitch-black shadow-[6px_6px_0_#000] transition-[flex,padding] duration-300 ease-out {activeBar ===

                'filter'

                    ? 'flex-1 justify-between px-8 py-4'

                    : 'h-14 w-14 flex-none justify-center p-0'}"

                style="--button-complement: #ff2e63"

            >

                {#if activeBar === "filter"}

                    <span class="mx-auto whitespace-nowrap">Filter</span>

                {/if}

                <span class="flex flex-none items-center gap-2">

                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5 flex-none">

                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                    </svg>

                    {#if activeBar === "filter"}

                        <svg

                            viewBox="0 0 12 8"

                            aria-hidden="true"

                            class="h-3 w-3 flex-none transition-transform duration-200 {filterOpen ? '' : 'rotate-180'}"

                        >

                            <polygon points="1,7 6,1 11,7" fill="currentColor" />

                        </svg>

                    {/if}

                </span>

            </button>



            <FilterDropdown bind:open={filterOpen} triggerEl={filterTriggerEl} />

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
