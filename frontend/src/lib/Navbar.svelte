<script>
    let active = "Barter Skill";
    const menus = [
        {
            name: 'Beranda',
            href: '/#/'
        },
        {
            name: 'Barter Skill',
            href: '/#/timeline'
        },
        {
            name: 'Leaderboard',
            href: '/#/leaderboard'
        }
    ];
</script>

<nav class="flex h-14 items-center rounded-full border-2 border-black bg-neon-yellow shadow-[6px_6px_0_#000] sm:h-16">

    {#each menus as menu}   
    <a href={menu.href} onclick={() => active = menu.name} class:active={active === menu.name} class="button-lift flex h-full items-center rounded-full px-4 font-mono text-xs text-black sm:px-7 sm:text-base" style="--button-complement: #ff006e">{menu.name}</a>
    {/each}
</nav>