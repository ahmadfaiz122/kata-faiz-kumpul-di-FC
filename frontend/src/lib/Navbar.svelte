<script>
    import { onMount } from 'svelte';
    import * as state from './sharedvar.svelte.js';
    let innerWidth = 0
    let currentPage;
    onMount(() => {
        currentPage=window.location.href.split("#")[1].split("/")[1];
    });
    const menus = [
        {
            name: 'Beranda',
            href: '/#/',
            alias: ''
        },
        {
            name: 'Barter Skill',
            href: '/#/timeline',
            alias: 'timeline'
        },
        {
            name: 'Leaderboard',
            href: '/#/leaderboard',
            alias: 'leaderboard'
        }
    ];
</script>

<svelte:window bind:innerWidth={innerWidth} />

<nav class="relative md:flex h-14 items-center rounded-full border-2 border-black bg-neon-yellow shadow-[6px_6px_0_#000] sm:h-16">
    {#if innerWidth < 640}
        <button type="button" onclick={state.toggleNavbarMenu} class="transition duration-180 ease-in-out hover:-translate-y-1 h-full flex items-center rounded-full px-4 font-mono text-xs text-black sm:px-7 sm:text-base" style="--button-complement: #ff006e"id="menu-button"
      aria-expanded={state.appState.isNavbarMenuOpen} aria-haspopup="true"><svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M4 6H20M4 12H20M4 18H20" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg><p class="m-3">Menu</p></button>
        {#if state.appState.isNavbarMenuOpen}
        <div
      class="absolute right-0 z-102 mt-4 w-47 overflow-hidden rounded-3xl border-2 border-black bg-pitch-black shadow-[10px_10px_0_#000]"
>
      <div class="py-1" role="none">
        <a href="/profile" class="mb-1 transition duration-180 ease-in-out hover:-translate-y-1 flex items-center gap-5 px-3 py-2 text-sm text-white hover:bg-laser-pink hover:text-black" onclick={state.closeNavbarMenu} role="menuitem" tabindex="-1">
            <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="browser" class="svg-inline--fa fa-browser w-[20px] h-[20px]" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#ffffff" d="M.3 89.5C.1 91.6 0 93.8 0 96l0 64L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-256 0-64c0-35.3-28.7-64-64-64L64 32c-2.2 0-4.4 .1-6.5 .3c-9.2 .9-17.8 3.8-25.5 8.2C21.8 46.5 13.4 55.1 7.7 65.5c-3.9 7.3-6.5 15.4-7.4 24zM48 160l416 0 0 256c0 8.8-7.2 16-16 16L64 432c-8.8 0-16-7.2-16-16l0-256z"></path></svg>
          <span class=" font-archivo text-xl font-bold">Beranda</span>
        </a>
        <a href="/#/timeline" class="mb-1 transition duration-180 ease-in-out hover:-translate-y-1 flex items-center gap-5 px-3 py-2 text-sm hover:bg-laser-pink text-white hover:text-black" onclick={state.closeNavbarMenu} role="menuitem" tabindex="-1">
        <svg width="20px" height="20px" role="img" viewBox="0 0 24 24" class="fill-white group-hover:fill-black" xmlns="http://www.w3.org/2000/svg">
<path d="M21 7.5L8 7.5M21 7.5L16.6667 3M21 7.5L16.6667 12" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4 16.5L17 16.5M4 16.5L8.33333 21M4 16.5L8.33333 12" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
          <span class=" font-archivo text-xl font-bold">Barter Skill</span>
        </a>
        <a href="/#/leaderboard" class="mb-1 transition duration-180 ease-in-out hover:-translate-y-1 flex items-center gap-5 px-3 py-2 text-sm hover:bg-laser-pink text-white hover:text-black" onclick={state.closeNavbarMenu} role="menuitem" tabindex="-1">
        <svg width="20px" height="20px" role="img" viewBox="0 0 24 24" class="fill-white group-hover:fill-black" xmlns="http://www.w3.org/2000/svg">
<path d="M15 21H9V12.6C9 12.2686 9.26863 12 9.6 12H14.4C14.7314 12 15 12.2686 15 12.6V21Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M20.4 21H15V18.1C15 17.7686 15.2686 17.5 15.6 17.5H20.4C20.7314 17.5 21 17.7686 21 18.1V20.4C21 20.7314 20.7314 21 20.4 21Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 21V16.1C9 15.7686 8.73137 15.5 8.4 15.5H3.6C3.26863 15.5 3 15.7686 3 16.1V20.4C3 20.7314 3.26863 21 3.6 21H9Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10.8056 5.11325L11.7147 3.1856C11.8314 2.93813 12.1686 2.93813 12.2853 3.1856L13.1944 5.11325L15.2275 5.42427C15.4884 5.46418 15.5923 5.79977 15.4035 5.99229L13.9326 7.4917L14.2797 9.60999C14.3243 9.88202 14.0515 10.0895 13.8181 9.96099L12 8.96031L10.1819 9.96099C9.94851 10.0895 9.67568 9.88202 9.72026 9.60999L10.0674 7.4917L8.59651 5.99229C8.40766 5.79977 8.51163 5.46418 8.77248 5.42427L10.8056 5.11325Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
          <span class=" font-archivo text-xl font-bold">Leaderboard</span>
        </a>
      </div>
    </div>
        {/if}  
    {/if}
    {#if innerWidth >= 640}
        {#each menus as menu}
        {#if currentPage === menu.alias}
            <a href={menu.href} class="button-lift flex bg-laser-pink h-full items-center rounded-full px-4 font-mono text-xs text-black sm:px-7 sm:text-base" style="--button-complement: #ff006e">{menu.name}</a>
        {:else}
            <a href={menu.href} class="button-lift flex h-full items-center rounded-full px-4 font-mono text-xs text-black sm:px-7 sm:text-base" style="--button-complement: #ff006e">{menu.name}</a>
        {/if}
        {/each}
    {/if}
</nav>