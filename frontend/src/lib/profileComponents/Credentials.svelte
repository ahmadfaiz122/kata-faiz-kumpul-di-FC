<script>
    export let user;
    const fallbackPhoto = 'https://i.pravatar.cc/150?img=12';
    $: socialLinks = [
        { label: 'LinkedIn', icon: 'in', href: user.profile?.linkedin },
        { label: 'Instagram', icon: 'IG', href: user.profile?.instagram },
        { label: 'GitHub', icon: 'GH', href: user.profile?.github },
    ].filter((social) => social.href);
    $: profileComplete = Boolean(user.profile?.username || user.profile?.bio || user.profile?.nim || socialLinks.length);
</script>

<div class="relative flex flex-col gap-4 overflow-hidden border-y border-pitch-black py-6 sm:min-h-40 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:py-7">
    <div class="relative z-10 flex min-w-0 items-center gap-3 min-[380px]:gap-4 sm:gap-5 md:gap-7">
        <div class="h-16 w-16 shrink-0 border-2 border-black bg-off-white shadow-[3px_3px_0_#000] min-[380px]:h-20 min-[380px]:w-20 sm:h-28 sm:w-28 sm:shadow-[6px_6px_0_#000] md:h-40 md:w-40">
            <img src={user.avatar || fallbackPhoto} class="h-full w-full object-cover" alt={`${user.name} profile`}>
        </div>
        <div class="min-w-0 flex-1">
            <p class="mb-2 w-max max-w-full truncate border-2 border-black bg-electric-cyan px-2 py-1 text-[10px] tracking-wider font-anton shadow-[2px_2px_0_#000] min-[380px]:text-xs sm:mb-3 sm:text-base sm:shadow-[4px_4px_0_#000]">Selamat Datang</p>
            <h1 class="truncate font-anton text-base leading-tight min-[380px]:text-xl sm:text-2xl sm:leading-none md:text-5xl">{user.name}</h1>
            <div class="mt-2 flex min-w-0 flex-wrap items-center gap-2 font-archivo text-[10px] min-[380px]:text-[11px] sm:mt-4 sm:gap-3 sm:text-xs md:text-sm">
                <p class="min-w-0 max-w-full truncate">{user.email}</p>
                {#if socialLinks.length}
                    <div class="hidden h-6 border-l border-black sm:block"></div>
                    <div class="flex gap-2">
                        {#each socialLinks as social}
                            <a href={social.href} target="_blank" rel="noreferrer" aria-label={social.label} title={social.label} class="flex h-6 min-w-6 items-center justify-center border-2 border-black bg-off-white px-1 font-mono text-[8px] font-bold hover:bg-neon-yellow">{social.icon}</a>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 right-0 hidden h-full w-[190px] bg-[#ffa174] [clip-path:polygon(35%_0,100%_0,100%_100%,0_100%,0_78%,15%_78%,15%_58%,30%_58%,30%_35%,45%_35%,45%_0)] sm:block md:w-1/3"></div>

    <a href="/#/edit-profile" title={profileComplete ? "Edit profile" : "Lengkapi profile"} class="button-lift relative z-10 flex w-full shrink-0 items-center justify-center border-2 border-black bg-off-white px-4 py-2.5 font-mono text-xs font-bold shadow-[2px_2px_0_#000] sm:mr-7 sm:w-auto sm:px-7 sm:py-4 sm:text-sm sm:shadow-[5px_5px_0_#000]" style="--button-complement: #ff006e">{profileComplete ? "Edit Profile" : "Lengkapi Profile"}</a>
</div>