<script>
    export let photo = "";
    export let index = "";
    export let title = "";
    export let color = "";
    const colors = {
        'neon-yellow': '#ffe477',
        'laser-pink': '#ff006e',
        'electric-cyan': '#2fc7b8'
    };
</script>

<div class="button-lift flex min-h-28 flex-col items-center justify-center border-2 border-black px-3 py-3 text-center shadow-[5px_5px_0_#000] sm:min-h-32" style="background-color: {colors[color] ?? '#fffdf5'}; --button-complement: #000000">
    {#if photo}
        <img src={photo} alt="" class="mb-1 h-6 w-6 object-contain" />
    {/if}
    <p class="font-mono text-lg font-black sm:text-xl">{index}</p>
    <p class="font-mono text-xs font-black sm:text-sm">{title}</p>
</div>