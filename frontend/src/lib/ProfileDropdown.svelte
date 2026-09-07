<script>
    import * as state from './sharedvar.svelte.js';

     let user = {
        name: 'Ibna',
        profile_photo: 'https://i.pravatar.cc/150?img=12'
    };

    function logout(){
        console.log("Logout clicked")
    }

</script>

<div class="relative z-101">
    <button type="button" onclick={state.toggleProfileMenu} class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] sm:gap-4 sm:px-6 sm:py-3" title="buttondropdown">
        <img src={user.profile_photo} alt={user.name} class="h-10 w-10 rounded-full object-cover border-2 border-black">
        <span class=" font-archivo md:font-normal font-bold md:text-xl text-sm">{user.name}</span>
        <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="chevron-down" class="profile-dropdown md:w-[20px] md:h-[20px] w-[15px] h-[15px]" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M239 401c9.4 9.4 24.6 9.4 33.9 0L465 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-175 175L81 175c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9L239 401z"></path></svg>
    </button>

    {#if state.appState.isProfileDropOpen}
    <style>
        .profile-dropdown{
            transition: transform 0.3s ease-in-out;
            transform: rotate(180deg);
        }
    </style>
        <div class=" absolute right-0 z-50 mt-4 w-45 overflow-hidden rounded-3xl border-2 border-black bg-pitch-black shadow-[10px_10px_0_#000]">
                <a href="/#/profile" onclick={state.closeProfileMenu} class="flex items-center gap-5 px-3 py-2 text-white hover:bg-neon-yellow hover:text-black">
                <span class="text-center text-2xl ">♙</span>
                <span class=" font-archivo text-xl font-bold">Profile</span>
            </a>

            <a href="/#/edit-profile" onclick={state.closeProfileMenu} class="flex items-center gap-5 px-3 py-2 text-white hover:bg-neon-yellow hover:text-black">
                <span class="text-center text-2xl">⚙</span>
                <span class="font-archivo text-xl font-bold">Settings</span>
            </a>

            <div class=" border-t-2 border-white"></div>

            <button type="button" onclick={logout} class="flex w-full items-center gap-5 px-3 py-2 text-left text-white hover:bg-neon-yellow hover:text-black">
                <span class="text-2xl">
                    ←
                </span>
                <span class="font-archivo text-xl font-bold">LogOut
                </span>
            </button>

        </div>
    {/if}  
    {#if !state.appState.isProfileDropOpen}
    <style>
        .profile-dropdown{
            transition: transform 0.3s ease-in-out;
            transform: rotate(0deg);
        }
    </style>
    {/if}
</div>