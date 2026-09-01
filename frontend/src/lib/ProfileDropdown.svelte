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

<div class=" relative ">
    <button type="button" onclick={toggleMenu} class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] sm:gap-4 sm:px-6 sm:py-3" title="buttondropdown">
        <img src={user.profile_photo} alt={user.name} class="h-10 w-10 rounded-full object-cover border-2 border-black">
        <span class=" font-archivo text-xl">{user.name}</span>
        <span class=" text-2xl">
            -
        </span>
    </button>

    {#if open}
        <div class=" absolute right-0 z-50 mt-4 w-48 overflow-hidden rounded-3xl border-2 border-black bg-pitch-black shadow-[10px_10px_0_#000]">
                <a href="/#/profile" onclick={closeMenu} class="flex items-center gap-5 px-3 py-2 text-white hover:bg-neon-yellow">
                <span class="text-center ml-1 text-2xl ">♙</span>
                <span class=" font-archivo text-2xl ml-2 font-bold">Profile</span>
            </a>
            
            <div class=" border-t-2 border-white"></div>

            <a href="/#/settings" onclick={closeMenu} class="flex items-center gap-5 px-3 py-2 text-white hover:bg-neon-yellow">
                <span class="text-center text-2xl">⚙</span>
                <span class="font-archivo text-2xl font-bold">Settings</span>
            </a>

            <div class=" border-t-2 border-white"></div>

            <button type="button" onclick={logout} class="flex w-full items-center gap-5 px-6 py-5 text-left text-white hover:bg-neon-yellow">
                <span class="text-4xl">
                    ←
                </span>
                <span class="font-archivo text-2xl font-bold">
                    <span>Log</span>
                    <span class=" pl-9">Out</span>
                </span>
            </button>

        </div>
    {/if}  
</div>