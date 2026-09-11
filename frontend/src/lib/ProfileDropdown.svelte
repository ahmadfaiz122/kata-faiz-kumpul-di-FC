<script>
    const user = {
        name: 'Ibna',
        profile_photo: 'https://i.pravatar.cc/150?img=12'
    };
</script>

<a href="/#/profile" class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] transition-transform hover:-translate-y-1 sm:gap-4 sm:px-6 sm:py-3" title="Buka profile">
        <img src={user.profile_photo} alt={user.name} class="h-10 w-10 rounded-full object-cover border-2 border-black">
        <span class=" font-archivo md:font-normal font-bold md:text-xl text-sm">{user.name}</span>
</a>
