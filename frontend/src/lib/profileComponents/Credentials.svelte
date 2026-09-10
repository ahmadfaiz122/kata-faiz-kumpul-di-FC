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


<div class="relative flex min-h-40 items-center justify-between overflow-hidden border-y border-pitch-black py-7">
    <div class="relative z-10 flex items-center">
        <div class="h-28 w-28 shrink-0 border-2 border-black bg-off-white shadow-[6px_6px_0_#000] sm:h-40 sm:w-40">
            <img src={user.avatar || fallbackPhoto} class="h-full w-full object-cover" alt={`${user.name} profile`}>
        </div>
        <div class="ml-5 sm:ml-7">
            <p class="mb-3 w-max border-2 border-black bg-electric-cyan px-2 py-1 font-medium shadow-[4px_4px_0_#000]">SELAMAT DATANG</p>
            <h1 class="max-w-140 font-anton leading-none md:text-5xl text-2xl">{user.name}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-3 font-archivo text-xs sm:text-sm">
                <p>{user.email}</p>
                <div class="hidden h-6 border-l border-black sm:block"></div>
                <div class="flex gap-2">
                    {#each socialLinks as social}
                        <a href={social.href} target="_blank" rel="noreferrer" aria-label={social.label} title={social.label} class="flex h-6 min-w-6 items-center justify-center border-2 border-black bg-off-white px-1 font-mono text-[8px] font-bold hover:bg-neon-yellow">{social.icon}</a>
                    {/each}
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 right-0 h-full md:w-1/3 w-[190px] bg-[#ffa174] [clip-path:polygon(35%_0,100%_0,100%_100%,0_100%,0_78%,15%_78%,15%_58%,30%_58%,30%_35%,45%_35%,45%_0)]"></div>
    <a href="/#/edit-profile" title={profileComplete ? "Edit profile" : "Lengkapi profile"} class="button-lift relative z-10 mr-1 shrink-0 border-2 border-black bg-off-white px-2 py-1 font-mono text-xs font-bold md:shadow-[5px_5px_0_#000] shadow-[2px_2px_0_#000] sm:mr-7 sm:px-7 sm:py-4 sm:text-sm" style="--button-complement: #ff006e">{profileComplete ? "Edit Profile" : "Lengkapi Profile"}</a>
</div>

