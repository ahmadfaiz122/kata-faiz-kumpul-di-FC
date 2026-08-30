<script>
    let open = false;

     let user = {
        name: 'Ibna',
        profile_photo: 'https://i.pravatar.cc/150?img=12'
    };

    function toggleMenu(){
        open = !open;
    };

    function closeMenu(){
        open = false;
    }

    function logout(){
        console.log("Logout clicked")
    }

</script>

<div class=" relative">
    <button type="button" onclick={toggleMenu} class=" flex items-center gap-4 rounded-full bg-electric-cyan border-2 border-black px-6 py-3 shadow-[8px_8px_0_#000]" title="buttondropdown">
        <img src={user.profile_photo} alt={user.name} class="h-10 w-10 rounded-full object-cover border-2 border-black">
        <span class=" font-archivo text-xl">{user.name}</span>
        <span class=" text-2xl">
            --
        </span>
    </button>

    {#if open}
        <div class=" absolute right-0 z-50 mt-4 w-80 overflow-hidden rounded-3xl border-2 border-black bg-pitch-black shadow-[10px_10px_0_#000]">
            <a href="/profile" onclick={closeMenu} class=" flex items-center gap-5 px-6 py-5 text-white hover:bg-neon-yellow hover:transition-colors duration-200">
                <span class=" text-4xl ">♙</span>
                <span class=" font-archivo text-2xl font-bold">Profile</span>
            </a>

            <a href="/settings" onclick={closeMenu} class="flex items-center gap-5 px-6 py-5 text-white hover:bg-neon-yellow hover:transition-colors duration-200">
                <span class="text-4xl">
                    ⚙
                </span>
                <span class="font-archivo text-2xl font-bold">
                    Settings
                </span>
            </a>

            <div class="mx-6 border-t-2 border-white"></div>

            <button type="button" onclick={logout} class="flex w-full items-center gap-5 px-6 py-5 text-left text-white hover:bg-neon-yellow hover:transition-colors duration-200">
                <span class="text-4xl">
                    ←
                </span>
                <span class="font-archivo text-2xl font-bold">
                    Log Out
                </span>
            </button>

        </div>
    {/if}  
</div>