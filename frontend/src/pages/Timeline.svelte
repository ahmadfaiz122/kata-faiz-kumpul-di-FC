<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import { onMount } from "svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import TimelinePost from "../lib/TimelinePost.svelte";

    editPage("Beranda");

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    /** @type {Array<{content: string, user?: {name?: string}, created_at: string, updated_at?: string}>} */
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
            posts = data.data ?? [];
        } catch (exception) {
            error = exception instanceof Error ? exception.message : "Feed tidak dapat dimuat.";
        } finally {
            loading = false;
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
            posts = [data, ...posts];
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
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"><img src="src/assets/logo.png" alt="Faiz Logo" class="h-full w-full scale-[1.2] object-contain" /></a>
        <Navbar />
        <div class="justify-self-end dashboard-enter"><ProfileDropdown /></div>
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
                        <div class="dashboard-enter" style="animation-delay: {index * 100}ms"><TimelinePost content={post.content} author={post.user?.name ?? "Unknown user"} createdAt={formatDate(post.created_at)} edited={isEdited(post)} /></div>
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
