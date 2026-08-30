<script>
    let active = "Barter Skill";
    const menus = [
        {
            name: 'Beranda',
            href: '/'
        },
        {
            name: 'Barter Skill',
            href: '/barter'
        },
        {
            name: 'Leaderboard',
            href: '/leaderboard'
        }
    ];
</script>

<nav
    class="flex items-center
           h-20
           rounded-full
           border-2 border-black
           bg-neon-yellow
           shadow-[7px_7px_0_#000]"
>
    {#each menus as menu}
        <a
            href={menu.href}
            onclick={() => active = menu.name}
            class="
                h-full
                flex items-center
                rounded-full
                px-8

                font-archivo
                text-xl
                text-black
                underline

                transition-colors
                duration-300
                ease-out

                hover:bg-laser-pink
            "
        >
            {menu.name}
        </a>
    {/each}
</nav>