<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import { onMount } from "svelte";
    import logo from "../assets/logo.webp";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import TimelinePost from "../lib/TimelinePost.svelte";

    editPage("Beranda");

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    /** @type {Array<{id: number, content: string, user?: {name?: string, avatar?: string}, created_at: string, updated_at?: string, likes_count?: number, comments_count?: number, liked_by_user?: boolean, comments?: Array<object>, commentsOpen?: boolean, commentText?: string, likeLoading?: boolean, commentLoading?: boolean}>} */
    let posts = [];
    let content = "";
    let loading = true;
    let submitting = false;
    let error = "";

    async function loadPosts() {
        loading = true;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/posts`);
            if (!response.ok) throw new Error("Feed tidak dapat dimuat.");
            const data = await response.json();
            posts = (data.data ?? []).map((post) => ({ ...post, comments: [], commentsOpen: false, commentText: "", likeLoading: false, commentLoading: false }));
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Feed tidak dapat dimuat.";
        } finally {
            loading = false;
        }
    }

    function authHeaders() {
        return { Accept: "application/json", "Content-Type": "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}` };
    }

    async function toggleLike(post) {
        if (post.likeLoading) return;
        const liked = post.liked_by_user ?? false;
        const likesCount = post.likes_count ?? 0;
        posts = posts.map((item) => item.id === post.id ? {
            ...item,
            liked_by_user: !liked,
            likes_count: Math.max(0, likesCount + (liked ? -1 : 1)),
            likeLoading: true
        } : item);
        try {
            const response = await fetch(`${backendUrl}/api/posts/${post.id}/like`, { method: "POST", headers: authHeaders() });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Like gagal diproses.");
            posts = posts.map((item) => item.id === post.id ? { ...item, liked_by_user: data.liked, likes_count: data.likes_count, likeLoading: false } : item);
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Like gagal diproses.";
            posts = posts.map((item) => item.id === post.id ? { ...item, liked_by_user: liked, likes_count: likesCount, likeLoading: false } : item);
        }
    }

    async function toggleComments(post) {
        if (post.commentsOpen) {
            posts = posts.map((item) => item.id === post.id ? { ...item, commentsOpen: false } : item);
            return;
        }
        try {
            const response = await fetch(`${backendUrl}/api/posts/${post.id}/comments`);
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Komentar gagal dimuat.");
            posts = posts.map((item) => item.id === post.id ? { ...item, comments: data.data ?? [], commentsOpen: true } : item);
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Komentar gagal dimuat.";
        }
    }

    function updateCommentText(post, event) {
        posts = posts.map((item) => item.id === post.id ? { ...item, commentText: event.currentTarget.value } : item);
    }

    async function submitComment(post) {
        const text = post.commentText?.trim();
        if (!text || post.commentLoading) return;
        posts = posts.map((item) => item.id === post.id ? { ...item, commentLoading: true } : item);
        try {
            const response = await fetch(`${backendUrl}/api/posts/${post.id}/comments`, { method: "POST", headers: authHeaders(), body: JSON.stringify({ content: text }) });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Komentar gagal dikirim.");
            posts = posts.map((item) => item.id === post.id ? { ...item, comments: [data, ...(item.comments ?? [])], comments_count: (item.comments_count ?? 0) + 1, commentText: "", commentLoading: false, commentsOpen: true } : item);
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Komentar gagal dikirim.";
            posts = posts.map((item) => item.id === post.id ? { ...item, commentLoading: false } : item);
        }
    }

    async function submitPost() {
        const trimmedContent = content.trim();
        if (!trimmedContent || submitting) return;

        submitting = true;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/posts`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}`
                },
                body: JSON.stringify({ content: trimmedContent })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Post gagal dibuat.");
            posts = [{ ...data, comments: [], commentsOpen: false, commentText: "", likeLoading: false, commentLoading: false }, ...posts];
            content = "";
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Post gagal dibuat.";
        } finally {
            submitting = false;
        }
    }

    /** @param {string} date */
    function formatDate(date) {
        return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium", timeStyle: "short" }).format(new Date(date));
    }

    function isEdited(post) {
        return Boolean(post.updated_at && new Date(post.updated_at).getTime() > new Date(post.created_at).getTime());
    }

    onMount(loadPosts);

    const leaderboard = ["Kastama", "Ibna", "Reinzal"];
</script>

<main class="min-h-screen bg-[#d8d8d8] px-5 py-6 sm:px-10 lg:px-14">
<div class="pointer-events-none absolute -right-20 -top-16 h-44 w-44 rounded-full border-2 border-pitch-black bg-laser-pink sm:h-60 sm:w-60 lg:h-72 lg:w-72"></div>
    <div class="pointer-events-none absolute -left-10 top-40 hidden h-24 w-24 -rotate-12 border-2 border-pitch-black bg-[#ffe477] sm:block sm:h-32 sm:w-32"></div>
    <div class="pointer-events-none absolute -bottom-16 -left-14 h-40 w-40 rounded-full border-2 border-pitch-black bg-electric-cyan sm:h-52 sm:w-52"></div>
    <div class="pointer-events-none absolute bottom-32 right-4 hidden h-14 w-14 rotate-6 border-2 border-pitch-black bg-cyber-lime lg:block"></div>
    
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-9 w-20 min-w-0 transition-transform hover:-translate-y-1 sm:h-11 sm:w-28 md:h-14 md:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class=" justify-self-end dashboard-enter">
            <ProfileDropdown />
        </div>
    </header>

    <div class="mx-auto mt-14 grid max-w-320 gap-8 lg:grid-cols-[minmax(0,3fr)_220px]">
        <section>
            <form class="border-2 border-pitch-black bg-off-white p-4 shadow-[6px_6px_0_#000]" on:submit|preventDefault={submitPost}>
                <label for="post-content" class="font-mono text-xs font-bold">Lagi penasaran sama apa nih?</label>
                <textarea id="post-content" bind:value={content} maxlength="2000" rows="3" placeholder="Bagikan sesuatu yang sedang kamu pelajari..." class="mt-3 w-full resize-y border-2 border-pitch-black bg-white p-3 font-mono text-xs outline-none focus:bg-[#fff7c7]"></textarea>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <span class="font-mono text-[10px] text-pitch-black/60">{content.length}/2000</span>
                    <button type="submit" disabled={submitting || !content.trim()} class="button-lift border-2 border-pitch-black bg-laser-pink px-6 py-2 font-mono text-xs font-bold text-off-white shadow-[3px_3px_0_#000] disabled:cursor-not-allowed disabled:opacity-50" style="--button-complement: #00d9ff">{submitting ? "Mengirim..." : "Post"}</button>
                </div>
            </form>

            {#if error}<p class="mt-4 border-2 border-pitch-black bg-[#ffe477] p-3 font-mono text-xs font-bold" role="alert">{error}</p>{/if}

            <div class="mt-8 space-y-7">
                {#if loading}
                    <p class="font-mono text-xs font-bold">Memuat timeline...</p>
                {:else if posts.length === 0}
                    <p class="font-mono text-xs font-bold">Belum ada post. Jadilah yang pertama berbagi.</p>
                {:else}
                    {#each posts as post, index}
                        <div class="dashboard-enter" style="animation-delay: {index * 100}ms"><TimelinePost content={post.content} author={post.user?.name ?? "Unknown user"} authorAvatar={post.user?.avatar ?? ""} createdAt={formatDate(post.created_at)} edited={isEdited(post)} liked={post.liked_by_user ?? false} likesCount={post.likes_count ?? 0} commentsCount={post.comments_count ?? 0} comments={post.comments ?? []} commentsOpen={post.commentsOpen ?? false} commentText={post.commentText ?? ""} likeLoading={post.likeLoading ?? false} commentLoading={post.commentLoading ?? false} onToggleLike={() => toggleLike(post)} onToggleComments={() => toggleComments(post)} onCommentInput={(event) => updateCommentText(post, event)} onSubmitComment={() => submitComment(post)} /></div>
                    {/each}
                {/if}
            </div>
        </section>

        <aside class="dashboard-enter dashboard-enter-delay-1 space-y-8 lg:pt-5">
            <div class="min-h-70 border-2 border-pitch-black bg-[#48b3cf] p-5 shadow-[10px_10px_0_#000]">
                <h2 class="font-mono text-sm font-bold leading-relaxed">Leaderboard<br />Minggu ini 🏆</h2>
                <ol class="mt-8 space-y-4 font-mono text-xs font-bold">
                    {#each leaderboard as user, index}
                        <li class="flex justify-between border-b border-pitch-black/40 pb-2"><span>0{index + 1} {user}</span><span>{100 - index * 12}</span></li>
                    {/each}
                </ol>
            </div>
            <div class="h-28 border-2 border-pitch-black bg-[#48b3cf] shadow-[10px_10px_0_#000]"></div>
            <div class="h-28 border-2 border-pitch-black bg-[#48b3cf] shadow-[10px_10px_0_#000]"></div>
        </aside>
    </div>
</main>
