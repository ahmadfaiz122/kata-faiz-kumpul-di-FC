<script>
    export let type = "text";
    export let content = "";

    const actions = [
        { label: "Like", icon: "♥" },
        { label: "Comment", icon: "●" },
        { label: "Share", icon: "↗" }
    ];
</script>

<article class="border-2 border-pitch-black bg-off-white shadow-[10px_10px_0_#000]">
    <header class="flex items-center justify-between bg-[#2fc7b8] px-3 py-2">
        <div class="flex items-center gap-3">
            <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-pitch-black bg-off-white text-xs">●</span>
            <span class="font-mono text-[10px] font-bold sm:text-xs">Ibna Clevaro Syarif's Post</span>
        </div>
        <div class="flex gap-2">
            <span class="h-4 w-4 rounded-full border border-pitch-black bg-laser-pink"></span>
            <span class="h-4 w-4 rounded-full border border-pitch-black bg-[#ffe477]"></span>
            <span class="h-4 w-4 rounded-full border border-pitch-black bg-[#67d986]"></span>
        </div>
    </header>

    <div class="px-4 py-8 sm:px-10 sm:py-10">
        {#if type === "text"}
            <p class="mx-auto max-w-140 text-center font-mono text-[10px] font-bold leading-relaxed sm:text-xs">{content}</p>
        {:else}
            <div class="flex h-24 items-center justify-center border-2 border-pitch-black bg-off-white font-mono text-xs shadow-[3px_3px_0_#000] sm:h-25">Photo</div>
        {/if}
    </div>

    <footer class="flex justify-end gap-2 px-3 pb-3 sm:gap-3 sm:px-5 sm:pb-4">
        {#each actions as action}
            <button type="button" class="button-lift flex min-w-23 items-center justify-center gap-2 border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000] sm:min-w-24 sm:text-xs" style="--button-complement: #ff006e">
                <span aria-hidden="true">{action.icon}</span>{action.label}
            </button>
        {/each}
    </footer>
</article>