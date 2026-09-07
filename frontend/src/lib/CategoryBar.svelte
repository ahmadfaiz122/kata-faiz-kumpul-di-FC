<script>
    import { createEventDispatcher } from 'svelte';
    import { slide } from 'svelte/transition';

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

    const categories = [
        {
            name: 'Education',
            color: 'bg-[#ffa174]',
            icon: 'education',
            complement: '#00d9ff'
        },
        {
            name: 'Technology',
            color: 'bg-laser-pink',
            icon: 'technology',
            complement: '#00d9ff'
        },
        {
            name: 'Business',
            color: 'bg-[#9b82e6]',
            icon: 'business',
            complement: '#ccff00'
        },
        {
            name: 'Language',
            color: 'bg-[#40b8d0]',
            icon: 'language',
            complement: '#ff006e'
        },
        {
            name: 'Art',
            color: 'bg-[#ffe477]',
            icon: 'art',
            complement: '#ff006e'
        },
        {
            name: 'Writing',
            color: 'bg-[#ffa174]',
            icon: 'writing',
            complement: '#00d9ff'
        }
    ];

    // Same paths as before, just centralized so the desktop pills and the
    // mobile dropdown rows render from one source instead of duplicating
    // six <svg> blocks twice.
    const iconPaths = {
        education: `
            <path d="M12 42L40 15L68 42" />
            <path d="M18 39V57L40 69L62 57V39" />
            <path d="M26 47L40 55L54 47" />
            <path d="M32 27L59 42" />
        `,
        technology: `
            <rect x="8" y="15" width="27" height="18" rx="2" />
            <path d="M14 38H29" />
            <rect x="42" y="9" width="17" height="28" rx="3" />
            <rect x="51" y="42" width="21" height="15" rx="2" />
            <path d="M47 62H68" />
            <path d="M28 48C35 40 48 40 55 48" />
            <path d="M32 54C38 48 45 48 51 54" />
        `,
        business: `
            <circle cx="28" cy="20" r="9" />
            <path d="M17 43C17 34 39 34 39 43V61H17V43Z" />
            <rect x="44" y="39" width="25" height="23" rx="2" />
            <path d="M49 39V34H64V39" />
            <path d="M44 48H69" />
        `,
        language: `
            <rect x="10" y="8" width="60" height="45" rx="2" />
            <path d="M21 19H49" />
            <path d="M35 15V42" />
            <path d="M20 27C25 36 32 39 42 41" />
            <path d="M49 21C44 31 39 35 29 39" />
            <path d="M46 55V68" />
            <path d="M38 68H55" />
            <path d="M51 53L64 68" />
        `,
        art: `
            <path d="M40 10C23 10 10 22 10 38C10 53 22 65 37 65H42C46 65 48 60 46 56C44 52 47 48 52 48H60C66 48 70 43 70 37C70 22 57 10 40 10Z" />
            <circle cx="27" cy="31" r="3" fill="black" />
            <circle cx="40" cy="25" r="3" fill="black" />
            <circle cx="54" cy="31" r="3" fill="black" />
            <circle cx="31" cy="44" r="3" fill="black" />
        `,
        writing: `
            <path d="M14 63C14 63 14 57 22 56L59 19" />
            <path d="M54 14L66 26" />
            <path d="M49 19L61 31" />
            <path d="M22 56L31 65" />
            <path d="M14 63L31 65" />
            <path d="M58 18L63 13L68 18L63 23" />
        `
    };

    function iconMarkup(name, size) {
        return `
            <svg
                viewBox="0 0 80 80"
                fill="none"
                stroke="black"
                stroke-width="3.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-[${size}px] h-[${size}px]"
            >
                ${iconPaths[name] ?? ''}
            </svg>
        `;
    }
</script>

<svelte:window on:keydown={handleKeydown} />

<div class="relative md:w-[816px] w-full font-archivo">
    <!-- ============================================================ -->
    <!-- DESKTOP / TABLET — pill bar, unchanged, visible >= 640px      -->
    <!-- ============================================================ -->
    <div
        class="
            relative
            hidden sm:flex
            items-center justify-between
            w-full
            min-h-[123px]
            bg-white
            border-2 border-pitch-black
            overflow-x-auto px-5 py-4 sm:px-8
            shadow-[5px_5px_0px_#000]
        "
    >
        <!-- Yellow accent di kiri -->
        <div class="absolute left-0 top-0 bottom-0 w-[22px] bg-neon-yellow"></div>

        <!-- Categories -->
        <div class="flex-1 flex items-center justify-evenly gap-5 ml-6 mr-5">
            {#each categories as category}
                <button
                    type="button"
                    title={category.name}
                    on:click={() => selectCategory(category)}
                    class="button-lift
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
                        duration-300
                        ease-out

                        hover:w-[235px]
                    "
                    style="--button-complement: {category.complement}"
                >
                    <!-- Icon -->
                    <div class="flex h-[71px] w-[71px] shrink-0 items-center justify-center">
                        {@html iconMarkup(category.icon, 58)}
                    </div>

                    <!-- Text ketika hover -->
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

    <!-- ============================================================ -->
    <!-- MOBILE — dropdown menu, visible < 640px                      -->
    <!-- ============================================================ -->
    <div class="relative sm:hidden w-full">
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
                            {@html iconMarkup(category.icon, 24)}
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
