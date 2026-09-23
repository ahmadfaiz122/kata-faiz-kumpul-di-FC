<script>
    export let type = "text";
    export let content = "";
    export let author = "Unknown user";
    /** @type {number|null} */
    export let authorId = null;
    export let authorAvatar = "";
    export let createdAt = "";
    export let edited = false;
    export let canManage = false;
    export let onEdit = () => {};
    export let onDelete = () => {};
    export let liked = false;
    export let likesCount = 0;
    export let commentsCount = 0;
    /** @type {Array<{content: string, user?: {name?: string}}>} */
    export let comments = [];
    export let commentsOpen = false;
    export let commentText = "";
    export let likeLoading = false;
    export let commentLoading = false;
    export let onToggleLike = () => {};
    export let onToggleComments = () => {};
    /** @type {(event: Event) => void} */
    export let onCommentInput = () => {};
    export let onSubmitComment = () => {};
</script>

<article class="border-2 border-pitch-black bg-off-white shadow-[10px_10px_0_#000]">
    <header class="flex items-center justify-between bg-[#2fc7b8] px-3 py-2">
        <div class="flex items-center gap-3">
            {#if authorAvatar}
                <img src={authorAvatar} alt={`${author} avatar`} class="h-7 w-7 rounded-full border-2 border-pitch-black object-cover" />
            {:else}
                <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-pitch-black bg-off-white text-xs">●</span>
            {/if}
            {#if authorId}
                <a href={`/#/profile/${authorId}`} class="author-link font-mono text-[10px] font-bold sm:text-xs">{author}'s Post</a>
            {:else}
                <span class="font-mono text-[10px] font-bold sm:text-xs">{author}'s Post</span>
            {/if}
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
        {/if}
        {#if createdAt}<p class="mt-4 text-center font-mono text-[9px] text-pitch-black/60">{createdAt}{#if edited} (edited){/if}</p>{/if}
    </div>

    <footer class="flex flex-wrap justify-end gap-2 px-3 pb-3 sm:gap-3 sm:px-5 sm:pb-4">
        {#if canManage}
            <button type="button" onclick={onEdit} class="button-lift border-2 border-pitch-black bg-[#ffe477] px-3 py-1 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000] sm:text-xs">Edit</button>
            <button type="button" onclick={onDelete} class="button-lift border-2 border-pitch-black bg-laser-pink px-3 py-1 font-mono text-[10px] font-bold text-off-white shadow-[3px_3px_0_#000] sm:text-xs">Hapus</button>
        {/if}
        <button type="button" disabled={likeLoading} onclick={onToggleLike} class:!bg-laser-pink={liked} class="button-lift flex min-w-23 items-center justify-center gap-2 border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000] disabled:opacity-50 sm:min-w-24 sm:text-xs" style="--button-complement: #ff006e"><span aria-hidden="true">♥</span>{likesCount} Like</button>
        <button type="button" onclick={onToggleComments} class="button-lift flex min-w-23 items-center justify-center gap-2 border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000] sm:min-w-24 sm:text-xs"><span aria-hidden="true">●</span>{commentsCount} Comment</button>
    </footer>

    {#if commentsOpen}
        <section class="border-t-2 border-pitch-black bg-[#f4f4f4] px-4 py-4 sm:px-5" aria-label="Komentar post">
            <div class="space-y-3">
                {#if comments.length === 0}
                    <p class="font-mono text-[10px] text-pitch-black/60">Belum ada komentar.</p>
                {:else}
                    {#each comments as comment}
                        <div class="border-2 border-pitch-black bg-off-white p-2 font-mono text-[10px]"><strong>{comment.user?.name ?? "Unknown user"}</strong><p class="mt-1">{comment.content}</p></div>
                    {/each}
                {/if}
            </div>
            <form class="mt-4 flex gap-2" onsubmit={(event) => { event.preventDefault(); onSubmitComment(); }}>
                <input value={commentText} oninput={onCommentInput} maxlength="1000" placeholder="Tulis komentar..." class="min-w-0 flex-1 border-2 border-pitch-black bg-white px-3 py-2 font-mono text-[10px] outline-none focus:bg-[#fff7c7]" />
                <button type="submit" disabled={commentLoading || !commentText.trim()} class="button-lift border-2 border-pitch-black bg-electric-cyan px-3 py-2 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000] disabled:opacity-50">Kirim</button>
            </form>
        </section>
    {/if}
</article>

<style>
    .author-link { text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 3px; }
    .author-link:hover { color: #ff006e; }
</style>