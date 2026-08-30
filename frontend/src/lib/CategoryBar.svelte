<script>
    const categories = [
        {
            name: 'Education',
            icon: '📚',
            color: 'bg-[#ffa174]'
        },
        {
            name: 'Technology',
            icon: '💻',
            color: 'bg-[#ff006e]'
        },
        {
            name: 'Business',
            icon: '💼',
            color: 'bg-[#9b82e6]'
        },
        {
            name: 'Language',
            icon: '文',
            color: 'bg-[#40b8d0]'
        },
        {
            name: 'Art',
            icon: '🎨',
            color: 'bg-[#ffe477]'
        },
        {
            name: 'Writing',
            icon: '✎',
            color: 'bg-[#ffa174]'
        }
    ];
</script>

<div class=" flex items-center gap-8 bg-[#d9d9d9] border-2 border-black px-12 py-3 shadow-[6px_6px_0_#000]">
    {#each categories as category}
        <button type="button" title="button" class=" group flex items-center h-24 w-24 shrink-0 overflow-hidden rounded-full border-2 border-black {category.color} transition-all duration-300 ease-out hover:w-72">
            <div class=" flex h-24 w-24 shrink-0 items-center justify-center text-4xl">
                {category.icon}
            </div>  
            <span class=" whitespace-nowrap pr-8 text-2xl font-archivo font-medium underline opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                {category.name}
            </span>
        </button>
    {/each}
</div>

