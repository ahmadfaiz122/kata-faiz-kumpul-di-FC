<script>
    import { onMount } from "svelte";
    import { tick } from "svelte";
    import { editPage } from "../lib/sharedvar.svelte.js";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import CategoryBar from "../lib/CategoryBar.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import SkillCard from "../lib/SkillCard.svelte";
    import logo from "../assets/logo.webp";
    import FilterDropdown from "../lib/FilterDropdown.svelte";

    editPage("Swapp");

    let searchQuery = $state("");
    let searchFocused = $state(false);
    let searchInput = $state();
    /** @type {Array<{id: number|string, duration: string, mentorName: string, instructor: string, category: string, credits: string, university: string}>} */
    let skillOffers = $state([]);
    let selectedCategory = $state("");
    let activeFilters = $state([]);
    let proposalsLoading = $state(true);
    let proposalsError = $state("");
    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";

    async function submitSearch() {
        searchQuery = searchQuery.trim();
        await loadProposals();
    }

    function updateSearch(event) {
        const value = event.currentTarget.value.slice(0, 100);
        searchQuery = value.replace(/[<>%_;`]/g, "");
    }
    let activeBar = $state("search");
    let filterOpen = $state(false)
    let filterTriggerEl = $state(/** @type {HTMLButtonElement|null} */ (null));
        async function selectSearch() {

        activeBar = "search";

        filterOpen = false;
            await tick();
            searchInput?.focus();

    }



    function selectFilter() {

        searchFocused = false;

        if (activeBar !== "filter") {

            activeBar = "filter";

            filterOpen = false;

        } else {

            filterOpen = !filterOpen;

        }

    }


    let visibleOffers = $derived(skillOffers);

    async function loadProposals() {
        proposalsLoading = true;
        proposalsError = "";
        const params = new URLSearchParams();
        if (searchQuery) params.set("search", searchQuery);
        if (selectedCategory) params.set("category", selectedCategory);
        for (const filter of activeFilters) params.set(`${filter.key}_min`, filter.low), params.set(`${filter.key}_max`, filter.high);

        try {
            const response = await fetch(`${backendUrl}/api/proposals?${params}`, { headers: { Accept: "application/json" } });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Post Swapp gagal dimuat.");
            skillOffers = (result.data ?? []).map((/** @type {Record<string, any>} */ proposal) => ({
                id: proposal.id,
                duration: `${proposal.hour ?? 1} HOUR`,
                mentorName: proposal.requester_user?.name ?? proposal.full_name ?? "Anonymous",
                instructor: proposal.skill_name,
                category: proposal.skill_category,
                credits: `${proposal.hour ?? 1} kredit`,
                rating: proposal.requester_user?.profile?.rating ?? 0,
                reputation: proposal.requester_user?.profile?.reputation ?? 0,
                university: proposal.city || "Community learner",
            }));
        } catch (requestError) {
            proposalsError = requestError instanceof Error ? requestError.message : "Post Swapp gagal dimuat.";
        } finally {
            proposalsLoading = false;
        }
    }

    async function selectCategory(event) {
        selectedCategory = String(event.detail.slug ?? event.detail.name ?? "");
        await loadProposals();
    }

    async function applyFilters(filters) {
        activeFilters = filters;
        await loadProposals();
    }

    async function resetFilters() {
        activeFilters = [];
        await loadProposals();
    }

    onMount(loadProposals);
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
<div class="pointer-events-none absolute -left-20 -top-20 h-44 w-44 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-60 sm:w-60 lg:h-72 lg:w-72"></div>
    <div class="pointer-events-none absolute -right-14 top-24 hidden h-20 w-20 rotate-12 border-2 border-pitch-black bg-electric-cyan sm:block sm:h-28 sm:w-28 lg:top-32"></div>
    <div class="pointer-events-none absolute -right-16 bottom-10 h-40 w-40 rounded-full border-2 border-pitch-black bg-laser-pink/90 sm:h-56 sm:w-56"></div>
    <div class="pointer-events-none absolute bottom-24 -left-2.5 hidden h-16 w-16 rotate-6 border-2 border-pitch-black bg-cyber-lime sm:block sm:h-20 sm:w-20"></div>
    
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-9 w-20 min-w-0 transition-transform hover:-translate-y-1 sm:h-11 sm:w-28 md:h-14 md:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class=" justify-self-end">
            <ProfileDropdown />
        </div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 flex max-w-7xl flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <CategoryBar on:categorySelect={selectCategory} />
        <div class="relative flex items-center gap-3 lg:w-100">
                        

            <button



                type="button"



                onclick={selectSearch}



                aria-expanded={activeBar === "search"}



                aria-label="Cari"



                class="button-lift flex items-center overflow-hidden rounded-full border-2 border-pitch-black font-archivo text-sm text-off-white shadow-[6px_6px_0_#000] transition-[background-color,flex,padding] duration-300 ease-out {searchFocused ? 'bg-laser-pink' : 'bg-white'} {activeBar ===



                'search'



                    ? 'flex-1 justify-between px-8 py-4'



                    : 'h-14 w-14 flex-none justify-center p-0'}"



                style="--button-complement: #00d9ff"



            >



                {#if activeBar === "search"}

                    <input bind:this={searchInput} value={searchQuery} oninput={updateSearch} maxlength="100" onkeydown={(event) => event.key === "Enter" && submitSearch()} onfocus={() => searchFocused = true} onblur={() => searchFocused = false} class="mx-auto whitespace-nowrap relative z-10 min-w-0 flex-1 bg-transparent font-archivo text-sm font-bold text-pitch-black outline-none placeholder:text-pitch-black" placeholder="Cari nama skill..." />

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



            <FilterDropdown bind:open={filterOpen} triggerEl={filterTriggerEl} onApply={applyFilters} onReset={resetFilters} />

        </div>







    </section>



    <section class="dashboard-enter dashboard-enter-delay-2 mx-auto mt-10 max-w-7xl" aria-labelledby="offers-title">

        <div class="mb-5 flex items-end justify-between gap-4">

            <div>

                <p class="font-mono text-sm uppercase tracking-wide text-laser-pink">Temukan teman belajar</p>

                <h1 id="offers-title" class="font-anton text-4xl uppercase sm:text-5xl">Skill terbaru</h1>

            </div>

            <span class="hidden font-mono text-sm sm:block">01 / 06</span>

        </div>



        <div class="grid gap-6 md:grid-cols-2">

            {#if proposalsLoading}

                <p class="border-2 border-pitch-black bg-off-white p-5 font-mono text-xs">Memuat post Swapp...</p>

            {:else if proposalsError}

                <p role="alert" class="border-2 border-pitch-black bg-[#ffd6df] p-5 font-mono text-xs">{proposalsError}</p>

            {:else if visibleOffers.length === 0}

                <p class="border-2 border-pitch-black bg-off-white p-5 font-mono text-xs">Belum ada post Swapp aktif.</p>

            {/if}

            {#each visibleOffers as offer, index}

                <div class:dashboard-enter={index === 0} class:dashboard-enter-delay-3={index === 1}>

                    <SkillCard {...offer} />

                </div>

            {/each}

        </div>



        <div class="flex justify-center py-16">

            <button type="button" class="button-lift border-2 border-pitch-black bg-off-white px-7 py-3 font-mono text-xs uppercase shadow-[4px_4px_0_#000]" style="--button-complement: #00d9ff">Load More</button>

        </div>

    </section>



    <a

        href="/#/add-proposal"

        aria-label="Buat proposal baru"

        title="Buat proposal baru"

        class="button-lift fixed bottom-6 right-6 z-40 flex h-16 w-16 items-center justify-center rounded-full border-3 border-pitch-black bg-laser-pink text-pitch-black shadow-[6px_6px_0_#000] sm:bottom-10 sm:right-10 sm:h-20 sm:w-20"

        style="--button-complement: #ccff00"

    >

        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-9 w-9 sm:h-11 sm:w-11">

            <path d="M12 5V19M5 12H19" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />

        </svg>

    </a>

</main>