<script>
    import { onMount } from 'svelte';
    import { link } from 'svelte-spa-router';
    import { scale } from 'svelte/transition';
    import { cubicOut } from 'svelte/easing';
    import * as state from './sharedvar.svelte.js';
    let innerWidth = 0
    let currentPage = 'timeline';
    let menuButtonEl;
    let panelStyle = '';

    function updateCurrentPage() {
        const hashPath = window.location.hash.replace(/^#\/?/, '').split('?')[0];
        currentPage = hashPath || 'timeline';
    }

    onMount(() => {
        updateCurrentPage();
        window.addEventListener('hashchange', updateCurrentPage);
        return () => window.removeEventListener('hashchange', updateCurrentPage);
    });

    // Menu shouldn't stay open if the layout jumps to desktop nav mid-session.
    $: if (innerWidth >= 800 && state.appState.isNavbarMenuOpen) state.closeNavbarMenu();

    function handleKeydown(event) {
        if (event.key === 'Escape' && state.appState.isNavbarMenuOpen) state.closeNavbarMenu();
    }

    // Computed fresh on every open so the panel is always anchored to the
    // button's real on-screen position, not to whatever wrapper happens to
    // contain it in a given page's header — that's what kept clipping off
    // the left edge on narrow screens.
    function computePanelPosition() {
        if (!menuButtonEl) return;
        const rect = menuButtonEl.getBoundingClientRect();
        const margin = 16;
        const width = Math.min(256, window.innerWidth - margin * 2);
        let left = rect.right - width;
        left = Math.min(Math.max(left, margin), window.innerWidth - width - margin);
        const top = rect.bottom + 10;
        panelStyle = `top:${top}px; left:${left}px; width:${width}px;`;
    }

    function handleMenuButtonClick() {
        if (!state.appState.isNavbarMenuOpen) computePanelPosition();
        state.toggleNavbarMenu();
    }

    const menus = [
        { name: 'Swapp', href: '/#/swapp', alias: 'swapp' },
        { name: 'Beranda', href: '/#/timeline', alias: 'timeline' },
        { name: 'Leaderboard', href: '/#/leaderboard', alias: 'leaderboard' }
    ];
</script>

<svelte:window bind:innerWidth={innerWidth} on:keydown={handleKeydown} />

<div class="relative min-w-0 max-w-full shrink-0">
    <nav class="relative flex h-9 max-w-full shrink-0 flex-nowrap items-center overflow-x-auto whitespace-nowrap rounded-full border-2 border-black bg-neon-yellow shadow-[6px_6px_0_#000] [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:h-16">
        {#if innerWidth < 800}
            <button
                type="button"
                bind:this={menuButtonEl}
                onclick={handleMenuButtonClick}
                class="button-lift flex h-full shrink-0 items-center gap-2 rounded-full px-3 font-mono text-xs text-black transition duration-180 ease-in-out sm:px-7 sm:text-base"
                style="--button-complement: #ff006e"
                id="menu-button"
                aria-expanded={state.appState.isNavbarMenuOpen}
                aria-controls="mobile-nav-panel"
                aria-haspopup="true"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
                    <path d="M4 6H20" stroke="#000000" stroke-width="2.4" stroke-linecap="round" class="origin-center transition-transform duration-200" style={state.appState.isNavbarMenuOpen ? 'transform: translateY(6px) rotate(45deg)' : ''} />
                    <path d="M4 12H20" stroke="#000000" stroke-width="2.4" stroke-linecap="round" class="origin-center transition-opacity duration-150" style={state.appState.isNavbarMenuOpen ? 'opacity:0' : 'opacity:1'} />
                    <path d="M4 18H20" stroke="#000000" stroke-width="2.4" stroke-linecap="round" class="origin-center transition-transform duration-200" style={state.appState.isNavbarMenuOpen ? 'transform: translateY(-6px) rotate(-45deg)' : ''} />
                </svg>
                <p class="m-0 shrink-0 max-[380px]:hidden">Menu</p>
            </button>
        {/if}
        {#if innerWidth >= 800}
            {#each menus as menu}
            {#if currentPage === menu.alias}
                <a href={menu.href} class="button-lift flex h-full shrink-0 items-center whitespace-nowrap rounded-full bg-laser-pink px-3 font-mono text-xs text-black lg:px-7 lg:text-base" style="--button-complement: #ff006e">{menu.name}</a>
            {:else}
                <a use:link href={menu.alias ? `/${menu.alias}` : '/'} class="button-lift flex h-full shrink-0 items-center whitespace-nowrap rounded-full px-3 font-mono text-xs text-black lg:px-7 lg:text-base" style="--button-complement: #ff006e">{menu.name}</a>
            {/if}
            {/each}
        {/if}
    </nav>

    {#if innerWidth < 800 && state.appState.isNavbarMenuOpen}
        <button type="button" class="fixed inset-0 z-101 cursor-default bg-pitch-black/40" aria-label="Close menu" onclick={state.closeNavbarMenu}></button>

        <div
            id="mobile-nav-panel"
            role="menu"
            aria-labelledby="menu-button"
            transition:scale={{ duration: 160, start: 0.92, opacity: 0, easing: cubicOut }}
            class="fixed z-102 overflow-hidden border-[3px] border-black bg-off-white shadow-[8px_8px_0_#000]"
            style={panelStyle}
        >
            <div class="flex items-center justify-between border-b-[3px] border-black bg-neon-yellow px-4 py-2.5">
                <span class="font-mono text-[11px] font-bold uppercase tracking-[0.14em] text-black">Navigasi</span>
                <span class="flex items-center gap-1.5">
                    <i class="block h-2.5 w-2.5 border-2 border-black bg-laser-pink"></i>
                    <i class="block h-2.5 w-2.5 border-2 border-black bg-off-white"></i>
                    <i class="block h-2.5 w-2.5 border-2 border-black bg-electric-cyan"></i>
                </span>
            </div>

            <div class="divide-y-2 divide-black">
                <a use:link href="/profile" class="group flex items-center gap-3 px-4 py-3 text-left transition-colors duration-150 hover:bg-laser-pink active:translate-x-[2px] active:translate-y-[2px]" onclick={state.closeNavbarMenu} role="menuitem" tabindex="-1">
                    <span class="grid h-9 w-9 shrink-0 place-items-center border-2 border-black bg-laser-pink shadow-[3px_3px_0_#000] transition-transform duration-150 group-hover:scale-110 group-hover:bg-off-white">
                        <svg width="16" height="16" viewBox="0 0 512 512" aria-hidden="true"><path fill="currentColor" class="text-off-white group-hover:text-black" d="M.3 89.5C.1 91.6 0 93.8 0 96l0 64L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-256 0-64c0-35.3-28.7-64-64-64L64 32c-2.2 0-4.4 .1-6.5 .3c-9.2 .9-17.8 3.8-25.5 8.2C21.8 46.5 13.4 55.1 7.7 65.5c-3.9 7.3-6.5 15.4-7.4 24zM48 160l416 0 0 256c0 8.8-7.2 16-16 16L64 432c-8.8 0-16-7.2-16-16l0-256z"/></svg>
                    </span>
                    <span class="flex-1 font-archivo text-base font-bold uppercase tracking-wide text-black group-hover:text-off-white">Swapp</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 shrink-0 -translate-x-1 opacity-0 transition-all duration-150 group-hover:translate-x-0 group-hover:stroke-off-white group-hover:opacity-100"><path d="M9 6l6 6-6 6" /></svg>
                </a>

                {#each menus.slice(1) as menu}
                    {@const active = currentPage === menu.alias}
                    <a href={menu.href} class="group relative flex items-center gap-3 px-4 py-3 text-left transition-colors duration-150 active:translate-x-[2px] active:translate-y-[2px] {active ? 'bg-neon-yellow' : 'hover:bg-laser-pink'}" onclick={state.closeNavbarMenu} role="menuitem" tabindex="-1" aria-current={active ? 'page' : undefined}>
                        {#if active}<i class="absolute left-0 top-0 h-full w-1.5 bg-black" aria-hidden="true"></i>{/if}
                        <span class="grid h-9 w-9 shrink-0 place-items-center border-2 border-black shadow-[3px_3px_0_#000] transition-transform duration-150 group-hover:scale-110 {active ? 'bg-off-white' : 'bg-white group-hover:bg-off-white'}">
                            {#if menu.alias === 'timeline'}
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M21 7.5L8 7.5M21 7.5L16.6667 3M21 7.5L16.6667 12" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4 16.5L17 16.5M4 16.5L8.33333 21M4 16.5L8.33333 12" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            {:else}
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M15 21H9V12.6C9 12.2686 9.26863 12 9.6 12H14.4C14.7314 12 15 12.2686 15 12.6V21Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20.4 21H15V18.1C15 17.7686 15.2686 17.5 15.6 17.5H20.4C20.7314 17.5 21 17.7686 21 18.1V20.4C21 20.7314 20.7314 21 20.4 21Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 21V16.1C9 15.7686 8.73137 15.5 8.4 15.5H3.6C3.26863 15.5 3 15.7686 3 16.1V20.4C3 20.7314 3.26863 21 3.6 21H9Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10.8056 5.11325L11.7147 3.1856C11.8314 2.93813 12.1686 2.93813 12.2853 3.1856L13.1944 5.11325L15.2275 5.42427C15.4884 5.46418 15.5923 5.79977 15.4035 5.99229L13.9326 7.4917L14.2797 9.60999C14.3243 9.88202 14.0515 10.0895 13.8181 9.96099L12 8.96031L10.1819 9.96099C9.94851 10.0895 9.67568 9.88202 9.72026 9.60999L10.0674 7.4917L8.59651 5.99229C8.40766 5.79977 8.51163 5.46418 8.77248 5.42427L10.8056 5.11325Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            {/if}
                        </span>
                        <span class="flex-1 font-archivo text-base font-bold uppercase tracking-wide text-black {active ? '' : 'group-hover:text-off-white'}">{menu.name}</span>
                        {#if active}
                            <span class="shrink-0 border-2 border-black bg-black px-1.5 py-0.5 font-mono text-[9px] font-bold text-off-white">DI SINI</span>
                        {:else}
                            <svg viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 shrink-0 -translate-x-1 opacity-0 transition-all duration-150 group-hover:translate-x-0 group-hover:stroke-off-white group-hover:opacity-100"><path d="M9 6l6 6-6 6" /></svg>
                        {/if}
                    </a>
                {/each}
            </div>
        </div>
    {/if}
</div>