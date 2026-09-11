<script>
    import { onMount } from "svelte";
    import indexImage1 from "../assets/star.png";
    import indexImage2 from "../assets/stats.png";
    import indexImage3 from "../assets/rank.png";
    import Bio from "../lib/profileComponents/Bio.svelte";
    import Credentials from "../lib/profileComponents/Credentials.svelte";
    import Index from "../lib/profileComponents/Index.svelte";
    import Achievement from "../lib/profileComponents/Achievement.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import TimelinePost from "../lib/TimelinePost.svelte";
    import logo from "../assets/logo.png";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    /** @typedef {{id: number, content: string, created_at: string, updated_at?: string, user?: {name?: string}}} ProfilePost */
    /** @type {{id: number, name?: string}|null} */
    let user = null;
    /** @type {ProfilePost[]} */
    let posts = [];
    let loading = true;
    let error = "";
    /** @type {number|null} */
    let editingPostId = null;
    let editContent = "";
    let savingPost = false;

    function logout() {
        localStorage.removeItem("auth_token");
        window.location.href = "/#/login";
    }

    /** @param {string} date */
    function formatDate(date) {
        return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium", timeStyle: "short" }).format(new Date(date));
    }

    /** @param {ProfilePost} post */
    function isEdited(post) {
        return Boolean(post.updated_at && new Date(post.updated_at).getTime() > new Date(post.created_at).getTime());
    }

    /** @param {ProfilePost} post */
    function startEditing(post) {
        editingPostId = post.id;
        editContent = post.content;
    }

    function cancelEditing() {
        editingPostId = null;
        editContent = "";
    }

    /** @param {number} postId */
    async function updatePost(postId) {
        const trimmedContent = editContent.trim();
        if (!trimmedContent || savingPost) return;

        savingPost = true;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/posts/${postId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}`,
                },
                body: JSON.stringify({ content: trimmedContent }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Post gagal diedit.");
            posts = posts.map((post) => post.id === postId ? data : post);
            cancelEditing();
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Post gagal diedit.";
        } finally {
            savingPost = false;
        }
    }

    /** @param {number} postId */
    async function deletePost(postId) {
        if (!window.confirm("Hapus post ini?")) return;

        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/posts/${postId}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}`,
                },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Post gagal dihapus.");
            posts = posts.filter((post) => post.id !== postId);
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Post gagal dihapus.";
        }
    }

    onMount(async () => {
        const token = localStorage.getItem("auth_token");

        if (!token) {
            window.location.href = "/#/login";
            return;
        }

        try {
            const response = await fetch(`${backendUrl}/api/user`, {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                },
            });

            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                window.location.href = "/#/login";
                return;
            }

            if (!response.ok) {
                throw new Error("Gagal mengambil data profile.");
            }

            user = await response.json();

            const postsResponse = await fetch(`${backendUrl}/api/user/posts`, {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                },
            });
            if (!postsResponse.ok) throw new Error("Post profile gagal dimuat.");
            posts = (await postsResponse.json()).data ?? [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profile.";
        } finally {
            loading = false;
        }
    });
</script>

<main class="min-h-screen overflow-hidden px-5 py-7 sm:px-10 lg:px-14">
    <header class="dashboard-enter mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-11 w-28 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] transition-transform hover:-translate-y-1 sm:h-14 sm:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full object-contain p-1">
        </a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>
    <div class="mx-auto mt-14 max-w-320">
        <div class="mb-5 flex justify-end">
            <button type="button" onclick={logout} class="button-lift border-2 border-pitch-black bg-laser-pink px-5 py-2 font-mono text-xs font-bold text-off-white shadow-[4px_4px_0_#000]" style="--button-complement: #00d9ff">Logout <span aria-hidden="true">→</span></button>
        </div>
        <div class="dashboard-enter relative z-100">
            {#if loading}
                <p class="border-y border-pitch-black py-10 text-center font-mono text-sm">Memuat profile...</p>
            {:else if error}
                <p class="border-y border-pitch-black py-10 text-center font-mono text-sm text-laser-pink">{error}</p>
            {:else}
                <Credentials user={user} />
            {/if}
        </div>

        {#if user}
        <section class="dashboard-enter dashboard-enter-delay-1 mt-7 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <Bio user={user} />
            <div class="grid gap-5 sm:grid-cols-3">
                <Index photo={indexImage1} index="4.5/5.0" title="Rating" color="neon-yellow" />
                <Index photo={indexImage2} index="100" title="Reputation" color="laser-pink" />
                <Index photo={indexImage3} index="10000/10000" title="Leaderboard Rank" color="electric-cyan" />
            </div>
        </section>

        <section class="dashboard-enter dashboard-enter-delay-2 mt-5 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <div class="flex min-h-40 flex-col justify-center bg-[#ffa174] px-8 py-7 shadow-[10px_10px_0_#000]">
                <span class="font-archivo text-6xl leading-none text-[#3d6cff] h-[30px] mb-3">“</span>
                <p class="font-mono text-sm font-bold">You can also call me by</p>
                <p class="mt-1 font-anton text-3xl uppercase">{user.profile?.alias || user.profile?.username || user.name}</p>
            </div>
            <Achievement achievements={user.profile?.achievements || []} />
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]">
            <h2 class="inline-block bg-pitch-black px-10 py-1 font-mono text-sm font-bold text-off-white">Skills</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                {#each user.profile?.skills || [] as skill}
                    <span class="border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-sm shadow-[3px_3px_0_#000]">{skill}</span>
                {/each}
            </div>
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7">
            <div class="mb-4 flex items-end justify-between border-b-2 border-pitch-black pb-2">
                <h2 class="font-anton text-3xl uppercase">My Posts</h2>
                <span class="font-mono text-xs font-bold">{posts.length} post</span>
            </div>
            {#if posts.length === 0}
                <p class="border-2 border-pitch-black bg-off-white p-5 font-mono text-xs font-bold shadow-[6px_6px_0_#000]">Belum ada post di profile kamu.</p>
            {:else}
                <div class="space-y-7">
                    {#each posts as post, index}
                        {#if editingPostId === post.id}
                            <form class="border-2 border-pitch-black bg-off-white p-4 shadow-[10px_10px_0_#000]" onsubmit={(event) => { event.preventDefault(); updatePost(post.id); }}>
                                <label for={`edit-post-${post.id}`} class="font-mono text-xs font-bold">Edit post</label>
                                <textarea id={`edit-post-${post.id}`} bind:value={editContent} maxlength="2000" rows="4" class="mt-3 w-full resize-y border-2 border-pitch-black bg-white p-3 font-mono text-xs outline-none focus:bg-[#fff7c7]"></textarea>
                                <div class="mt-3 flex justify-end gap-3">
                                    <button type="button" onclick={cancelEditing} class="border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-xs font-bold">Batal</button>
                                    <button type="submit" disabled={savingPost || !editContent.trim()} class="button-lift border-2 border-pitch-black bg-laser-pink px-4 py-1 font-mono text-xs font-bold text-off-white shadow-[3px_3px_0_#000] disabled:opacity-50">{savingPost ? "Menyimpan..." : "Simpan"}</button>
                                </div>
                            </form>
                        {:else}
                            <div class="dashboard-enter" style="animation-delay: {index * 100}ms">
                                <TimelinePost content={post.content} author={post.user?.name ?? user.name} createdAt={formatDate(post.created_at)} edited={isEdited(post)} canManage={true} onEdit={() => startEditing(post)} onDelete={() => deletePost(post.id)} />
                            </div>
                        {/if}
                    {/each}
                </div>
            {/if}
        </section>
        {/if}
    </div>
</main>