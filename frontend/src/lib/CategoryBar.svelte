<script>
    import { createEventDispatcher } from 'svelte';
    import { onMount } from 'svelte';
    import { slide } from 'svelte/transition';
    import businessIcon from '../assets/category/business.svg';
    import educationIcon from '../assets/category/edu.svg';
    import languageIcon from '../assets/category/language.svg';
    import technologyIcon from '../assets/category/tech.svg';
    import writingIcon from '../assets/category/writing.svg';
    import artIcon from '../assets/category/art.svg';

    const dispatch = createEventDispatcher();

    let open = false;

    function toggleOpen() {
        open = !open;
    }

    function closeMenu() {
        open = false;
    }

    function selectCategory(category) {
        dispatch('categorySelect', category);
        closeMenu();
    }

    function handleKeydown(event) {
        if (event.key === 'Escape') closeMenu();
    }

    const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';
    const categoryStyles = {
        education: { color: 'bg-[#ffa174]', icon: educationIcon, complement: '#00d9ff' },
        technology: { color: 'bg-laser-pink', icon: technologyIcon, complement: '#00d9ff' },
        business: { color: 'bg-[#9b82e6]', icon: businessIcon, complement: '#ccff00' },
        language: { color: 'bg-[#40b8d0]', icon: languageIcon, complement: '#ff006e' },
        art: { color: 'bg-[#ffe477]', icon: artIcon, complement: '#ff006e' },
        writing: { color: 'bg-[#ffa174]', icon: writingIcon, complement: '#00d9ff' }
    };
    const fallbackCategoryNames = ['Education', 'Technology', 'Business', 'Language', 'Art', 'Writing'].map((name, index) => ({ id: index + 1, name, slug: name.toLowerCase() }));
    let categories = fallbackCategoryNames.map(categoryView);

    function categoryView(category) {
        const name = typeof category === 'string' ? category : category.name;
        const key = name.toLowerCase();
        return { ...category, name, ...(categoryStyles[key] ?? { color: 'bg-neon-yellow', icon: technologyIcon, complement: '#00d9ff' }) };
    }

    onMount(async () => {
        try {
            const response = await fetch(`${backendUrl}/api/proposals/categories`);
            if (!response.ok) return;
            const result = await response.json();
            const serverCategories = (result.data ?? []).filter(Boolean).map(categoryView);
            if (serverCategories.length > 0) categories = serverCategories;
        } catch {
            // Keep the default categories visible when the API is unavailable.
        }
    });

</script>

<svelte:window on:keydown={handleKeydown} />

<div class="relative w-full min-[1200px]:min-w-[750px] min-[1200px]:max-w-[816px] min-[1200px]:[flex:1_1_816px] transition-[width] duration-300 ease-out font-archivo">
    <div
        class="
            relative
            hidden min-[1200px]:flex
            items-center justify-between
            w-full
            min-h-[123px]
            bg-white
            border-2 border-pitch-black
            overflow-hidden px-5 py-4 sm:px-8
            shadow-[5px_5px_0px_#000]
        "
    >
        <!-- Yellow accent di kiri -->
        <div class="absolute left-0 top-0 bottom-0 w-[22px] bg-neon-yellow"></div>

        <!-- Categories -->
        <div class="category-list flex-1 flex items-center justify-evenly ml-6 mr-5">
            {#each categories as category}
                <button
                    type="button"
                    title={category.name}
                    on:click={() => selectCategory(category)}
                    class="category-button button-lift
                        group
                        flex items-center
                        h-[77px]
                        w-[77px]
                        shrink-0
                        overflow-hidden

                        rounded-full
                        border-[3px]
                        border-pitch-black

                        {category.color}

                        transition-all
                        duration-700
                        
                        ease-out
                    "
                    style="--button-complement: {category.complement}"
                >
                    <!-- Icon -->
                    <div class="flex h-[71px] w-[71px] shrink-0 items-center justify-center">
                        <img src={category.icon} alt="" class:category-icon-small={category.name === 'Art' || category.name === 'Writing'} class="h-[58px] w-[58px] object-contain">
                    </div>

                    <span
                        class="
                            whitespace-nowrap
                            pr-6
                            text-xl
                            font-archivo
                            font-semibold
                            text-pitch-black

                            opacity-0
                            group-hover:opacity-100

                            transition-opacity
                            duration-200
                        "
                    >
                        {category.name}
                    </span>
                </button>
            {/each}
        </div>

        <!-- 3 kotak kecil di kanan -->
        <div class="flex flex-col items-center justify-center gap-3 w-5">
            <span class="w-[15px] h-[15px] bg-neon-yellow border-2 border-pitch-black"></span>
            <span class="w-[15px] h-[15px] bg-laser-pink border-2 border-pitch-black"></span>
            <span class="w-[15px] h-[15px] bg-[#4ade80] border-2 border-pitch-black"></span>
        </div>
    </div>

    <style>
        .category-icon-small {
            opacity: 0.85;
            transform: scale(0.82);
        }

        .category-list {
            gap: 1.25rem;
            min-width: 0;
            transition: gap 300ms ease;
        }

        .category-button {
            width: 77px;
            flex: 0 1 77px;
            transition: width 300ms ease, flex-basis 300ms ease, transform 180ms ease, box-shadow 180ms ease;
        }

        .category-button:hover,
        .category-button:focus-visible {
            width: 235px;
            flex-basis: 235px;
        }

        .category-list:has(.category-button:hover),
        .category-list:has(.category-button:focus-visible) {
            gap: 0;
        }
    </style>

    <!-- ============================================================ -->
    <!-- COMPACT — dropdown menu, visible until there's enough room    -->
    <!-- for the full icon bar next to the search/filter row           -->
    <!-- ============================================================ -->
    <div class="relative min-[1200px]:hidden w-full">
        <button
            type="button"
            on:click={toggleOpen}
            aria-expanded={open}
            aria-haspopup="listbox"
            class="
                relative flex items-center justify-between
                w-full
                bg-white
                border-2 border-pitch-black
                pl-8 pr-4 py-4
                overflow-hidden
                shadow-[5px_5px_0px_#000]
                transition-all duration-150
                active:shadow-[2px_2px_0px_#000]
                active:translate-x-[3px] active:translate-y-[3px]
            "
        >
            <!-- Yellow accent bar, same language as the desktop bar -->
            <span class="absolute left-0 top-0 bottom-0 w-[14px] bg-neon-yellow border-r-2 border-pitch-black"></span>

            <span class="text-lg font-archivo font-bold uppercase tracking-wide text-pitch-black">
                Categories
            </span>

            <span class="flex items-center gap-3">
                <!-- three little squares, mirroring the desktop bar's accent -->
                <span class="flex items-center gap-1.5">
                    <span class="w-[9px] h-[9px] bg-neon-yellow border-2 border-pitch-black"></span>
                    <span class="w-[9px] h-[9px] bg-laser-pink border-2 border-pitch-black"></span>
                    <span class="w-[9px] h-[9px] bg-[#4ade80] border-2 border-pitch-black"></span>
                </span>

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="black"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-5 w-5 shrink-0 transition-transform duration-200 {open ? 'rotate-180' : ''}"
                >
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </span>
        </button>

        {#if open}
            <!-- Invisible backdrop so tapping outside the panel closes it -->
            <button
                type="button"
                class="fixed inset-0 z-40 cursor-default bg-transparent"
                aria-label="Close category menu"
                on:click={closeMenu}
            ></button>

            <div
                transition:slide={{ duration: 180 }}
                class="
                    absolute left-0 right-0 top-[calc(100%+10px)]
                    z-50
                    bg-white
                    border-2 border-pitch-black
                    shadow-[5px_5px_0px_#000]
                    divide-y-2 divide-pitch-black
                    overflow-hidden
                "
                role="listbox"
            >
                {#each categories as category}
                    <button
                        type="button"
                        role="option"
                        aria-selected="false"
                        on:click={() => selectCategory(category)}
                        class="
                            group flex w-full items-center gap-4
                            px-4 py-3.5
                            text-left
                            bg-white
                            transition-colors duration-150
                            hover:bg-black/[0.04]
                            active:bg-black/[0.08]
                        "
                    >
                        <span
                            class="
                                flex h-11 w-11 shrink-0 items-center justify-center
                                rounded-full
                                border-2 border-pitch-black
                                {category.color}
                                transition-transform duration-150
                                group-hover:scale-110
                            "
                        >
                            <img src={category.icon} alt="" class="h-6 w-6 object-contain">
                        </span>

                        <span class="flex-1 text-base font-archivo font-bold uppercase tracking-wide text-pitch-black">
                            {category.name}
                        </span>

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="black"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-4 w-4 shrink-0 opacity-0 -translate-x-1 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0"
                        >
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </button>
                {/each}
            </div>
        {/if}
    </div>
</div>