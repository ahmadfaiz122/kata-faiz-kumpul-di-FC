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

<div class="button-lift flex min-h-32 flex-col items-center justify-center border-2 border-black px-3 py-4 text-center shadow-[6px_6px_0_#000] sm:min-h-40" style="background-color: {colors[color] ?? '#fffdf5'}; --button-complement: #000000">
    {#if photo}
        <img src={photo} alt="" class="mb-1 h-7 w-7 object-contain" />
    {/if}
    <p class="font-mono text-xl font-black sm:text-2xl">{index}</p>
    <p class="font-mono text-sm font-black sm:text-base">{title}</p>
</div>