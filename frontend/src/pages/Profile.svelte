<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import indexImage1 from "../assets/star.webp";
    import indexImage2 from "../assets/stats.webp";
    import indexImage3 from "../assets/rank.webp";
    import Bio from "../lib/profileComponents/Bio.svelte";
    import Credentials from "../lib/profileComponents/Credentials.svelte";
    import Index from "../lib/profileComponents/Index.svelte";
    import Achievement from "../lib/profileComponents/Achievement.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import TimelinePost from "../lib/TimelinePost.svelte";
    import logo from "../assets/logo.webp";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    /** @typedef {{id: number, content: string, created_at: string, updated_at?: string, likes_count?: number, comments_count?: number, liked_by_user?: boolean, user?: {name?: string, avatar?: string}, comments?: Array<{content: string, user?: {name?: string}}>, commentsOpen?: boolean, commentText?: string, likeLoading?: boolean, commentLoading?: boolean}} ProfilePost */
    /** @type {{id: number, name?: string, profile?: {alias?: string, username?: string, skills?: string[], skill_records?: Array<{name: string, category_skills?: string, material_path?: string}>, achievements?: string[], achievement_records?: Array<{name: string, description?: string, levels?: string, tanggal_terbit?: number, kadaluwarsa?: number, certificate_path?: string}>}}|null} */
    let user = null;
    /** @type {ProfilePost[]} */
    let posts = [];
    let loading = true;
    let error = "";
    /** @type {number|null} */
    let editingPostId = null;
    let editContent = "";
    let savingPost = false;
    /** @type {Array<{id: number|string, full_name?: string, email?: string, phone?: string, city?: string, skill_name?: string, skill_category?: string, skill_description?: string, proposal_path?: string}>} */
    let swappPosts = [];
    /** @type {number|string|null} */
    let editingSwappId = null;
    let swappSaving = false;
    let swappError = "";
    let swappForm = { full_name: "", email: "", phone: "", city: "", skill_name: "", skill_category: "", skill_description: "" };
    /** @type {File|null} */
    let swappFile = null;

    function authHeaders() {
        return { Accept: "application/json", "Content-Type": "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}` };
    }

    /** @param {ProfilePost} post */
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
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Like gagal diproses.";
            posts = posts.map((item) => item.id === post.id ? { ...item, liked_by_user: liked, likes_count: likesCount, likeLoading: false } : item);
        }
    }

    /** @param {ProfilePost} post */
    async function toggleComments(post) {
        if (post.commentsOpen) {
            posts = posts.map((item) => item.id === post.id ? { ...item, commentsOpen: false } : item);
            return;
        }
        try {
            const response = await fetch(`${backendUrl}/api/posts/${post.id}/comments`, { headers: { Accept: "application/json" } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Komentar gagal dimuat.");
            posts = posts.map((item) => item.id === post.id ? { ...item, comments: data.data ?? [], commentsOpen: true } : item);
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Komentar gagal dimuat.";
        }
    }

    /** @param {ProfilePost} post @param {Event} event */
    function updateCommentText(post, event) {
        const input = /** @type {HTMLInputElement} */ (event.currentTarget);
        posts = posts.map((item) => item.id === post.id ? { ...item, commentText: input.value } : item);
    }

    /** @param {ProfilePost} post */
    async function submitComment(post) {
        const text = post.commentText?.trim();
        if (!text || post.commentLoading) return;
        posts = posts.map((item) => item.id === post.id ? { ...item, commentLoading: true } : item);
        try {
            const response = await fetch(`${backendUrl}/api/posts/${post.id}/comments`, { method: "POST", headers: authHeaders(), body: JSON.stringify({ content: text }) });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? "Komentar gagal dikirim.");
            posts = posts.map((item) => item.id === post.id ? { ...item, comments: [data, ...(item.comments ?? [])], comments_count: (item.comments_count ?? 0) + 1, commentText: "", commentLoading: false, commentsOpen: true } : item);
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Komentar gagal dikirim.";
            posts = posts.map((item) => item.id === post.id ? { ...item, commentLoading: false } : item);
        }
    }

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

    /** @param {typeof swappPosts[number]} proposal */
    function startEditingSwapp(proposal) {
        editingSwappId = proposal.id;
        swappForm = {
            full_name: proposal.full_name ?? "",
            email: proposal.email ?? "",
            phone: proposal.phone ?? "",
            city: proposal.city ?? "",
            skill_name: proposal.skill_name ?? "",
            skill_category: proposal.skill_category ?? "",
            skill_description: proposal.skill_description ?? "",
        };
        swappFile = null;
        swappError = "";
    }

    function cancelEditingSwapp() {
        editingSwappId = null;
        swappFile = null;
        swappError = "";
    }

    async function updateSwapp() {
        if (!editingSwappId || swappSaving) return;
        swappSaving = true;
        swappError = "";
        try {
            const body = new FormData();
            for (const [key, value] of Object.entries(swappForm)) body.append(key, value.trim());
            if (swappFile) body.append("proposal_file", swappFile);
            const response = await fetch(`${backendUrl}/api/user/proposals/${editingSwappId}`, {
                method: "POST",
                headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}` },
                body,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message ?? "Post Swapp gagal diedit.");
            swappPosts = swappPosts.map((item) => String(item.id) === String(editingSwappId) ? data.data : item);
            cancelEditingSwapp();
        } catch (requestError) {
            swappError = requestError instanceof Error ? requestError.message : "Post Swapp gagal diedit.";
        } finally {
            swappSaving = false;
        }
    }

    /** @param {number|string} proposalId @param {string} proposalName */
    async function deleteSwapp(proposalId, proposalName) {
        if (!window.confirm(`Hapus post Swapp "${proposalName}"? Data ini tidak dapat dikembalikan.`)) return;
        swappError = "";
        try {
            const response = await fetch(`${backendUrl}/api/user/proposals/${proposalId}`, {
                method: "DELETE",
                headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token") ?? ""}` },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message ?? "Post Swapp gagal dihapus.");
            swappPosts = swappPosts.filter((item) => String(item.id) !== String(proposalId));
        } catch (requestError) {
            swappError = requestError instanceof Error ? requestError.message : "Post Swapp gagal dihapus.";
        }
    }

    onMount(async () => {
        const token = localStorage.getItem("auth_token");

        if (!token) {
            push("/login");
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
                push("/login");
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
            /** @type {ProfilePost[]} */
            const profilePosts = (await postsResponse.json()).data ?? [];
            posts = profilePosts.map((/** @type {ProfilePost} */ post) => ({ ...post, comments: [], commentsOpen: false, commentText: "", likeLoading: false, commentLoading: false }));
            const swappResponse = await fetch(`${backendUrl}/api/user/proposals`, {
                headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
            });
            if (!swappResponse.ok) throw new Error("Post Swapp gagal dimuat.");
            swappPosts = (await swappResponse.json()).data ?? [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profile.";
        } finally {
            loading = false;
        }
    });
</script>

<main class="min-h-screen overflow-hidden px-5 py-7 sm:px-10 lg:px-14">
    <header class="dashboard-enter mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"><img src="src/assets/logo.webp" alt="Faiz Logo" class="h-full w-full scale-[1.2] object-contain" /></a>
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
                <span class="font-anton text-6xl leading-none text-[#3d6cff] h-[30px] mb-3">“</span>
                <p class="font-mono text-sm font-bold">You can also call me by</p>
                <p class="mt-1 font-anton text-3xl uppercase">{user.profile?.alias || user.profile?.username || user.name}</p>
            </div>
            <Achievement achievements={user.profile?.achievement_records || []} />
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]">
            <h2 class="inline-block bg-pitch-black px-10 py-1 font-mono text-sm font-bold text-off-white">Skills</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                {#each user.profile?.skill_records || [] as skill}
                    <div class="border-2 border-pitch-black bg-off-white px-3 py-2 font-mono text-sm shadow-[3px_3px_0_#000]"><span>{skill.name}</span>{#if skill.material_path}<a href={skill.material_path} target="_blank" rel="noreferrer" class="mt-1 block text-[10px] underline">Buka materi PDF ↗</a>{/if}</div>
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
                                <TimelinePost content={post.content} author={post.user?.name ?? user.name} authorAvatar={post.user?.avatar ?? ""} createdAt={formatDate(post.created_at)} edited={isEdited(post)} liked={post.liked_by_user ?? false} likesCount={post.likes_count ?? 0} commentsCount={post.comments_count ?? 0} comments={post.comments ?? []} commentsOpen={post.commentsOpen ?? false} commentText={post.commentText ?? ""} likeLoading={post.likeLoading ?? false} commentLoading={post.commentLoading ?? false} onToggleLike={() => toggleLike(post)} onToggleComments={() => toggleComments(post)} onCommentInput={(event) => updateCommentText(post, event)} onSubmitComment={() => submitComment(post)} canManage={true} onEdit={() => startEditing(post)} onDelete={() => deletePost(post.id)} />
                            </div>
                        {/if}
                    {/each}
                </div>
            {/if}
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7">
            <div class="mb-4 flex items-end justify-between border-b-2 border-pitch-black pb-2">
                <h2 class="font-anton text-3xl uppercase">My Swapp Posts</h2>
                <span class="font-mono text-xs font-bold">{swappPosts.length} post</span>
            </div>
            {#if swappError}<p class="mb-4 border-2 border-pitch-black bg-[#ffe477] p-3 font-mono text-xs font-bold" role="alert">{swappError}</p>{/if}
            {#if swappPosts.length === 0}
                <p class="border-2 border-pitch-black bg-off-white p-5 font-mono text-xs font-bold shadow-[6px_6px_0_#000]">Belum ada post Swapp di profile kamu.</p>
            {:else}
                <div class="space-y-5">
                    {#each swappPosts as proposal}
                        {#if String(editingSwappId) === String(proposal.id)}
                            <form class="grid gap-4 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]" onsubmit={(event) => { event.preventDefault(); updateSwapp(); }}>
                                <h3 class="font-mono text-sm font-bold">Edit post Swapp</h3>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <input bind:value={swappForm.full_name} required placeholder="Nama lengkap" class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs" />
                                    <input bind:value={swappForm.email} required type="email" placeholder="Email" class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs" />
                                    <input bind:value={swappForm.phone} required placeholder="Nomor telepon" class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs" />
                                    <input bind:value={swappForm.city} required placeholder="Kota" class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs" />
                                    <input bind:value={swappForm.skill_name} required placeholder="Nama skill" class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs" />
                                    <select bind:value={swappForm.skill_category} required class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs"><option value="">Pilih kategori</option><option>Education</option><option>Technology</option><option>Business</option><option>Language</option><option>Art</option><option>Writing</option></select>
                                </div>
                                <textarea bind:value={swappForm.skill_description} required maxlength="5000" rows="4" placeholder="Deskripsi skill" class="border-2 border-pitch-black bg-white p-3 font-mono text-xs"></textarea>
                                <label class="font-mono text-xs">Ganti file proposal (opsional)<input type="file" accept="application/pdf,.pdf" onchange={(event) => swappFile = event.currentTarget.files?.[0] ?? null} class="mt-2 block w-full border-2 border-pitch-black bg-white p-2" /></label>
                                <div class="flex justify-end gap-3"><button type="button" onclick={cancelEditingSwapp} class="border-2 border-pitch-black bg-off-white px-3 py-2 font-mono text-xs font-bold">Batal</button><button type="submit" disabled={swappSaving} class="button-lift border-2 border-pitch-black bg-laser-pink px-4 py-2 font-mono text-xs font-bold text-off-white shadow-[3px_3px_0_#000] disabled:opacity-50">{swappSaving ? "Menyimpan..." : "Simpan"}</button></div>
                            </form>
                        {:else}
                            <article class="border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]">
                                <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-anton text-2xl uppercase">{proposal.skill_name}</h3><p class="font-mono text-[10px]">{proposal.skill_category} · {proposal.city} · {proposal.hour ?? 1} jam</p></div><div class="flex gap-2"><button type="button" onclick={() => startEditingSwapp(proposal)} class="border-2 border-pitch-black bg-[#ffe477] px-3 py-1 font-mono text-[10px] font-bold shadow-[2px_2px_0_#000]">Edit</button><button type="button" onclick={() => deleteSwapp(proposal.id, proposal.skill_name ?? "ini")} class="border-2 border-pitch-black bg-laser-pink px-3 py-1 font-mono text-[10px] font-bold text-off-white shadow-[2px_2px_0_#000]">Hapus</button></div></div>
                                <p class="mt-4 font-mono text-xs leading-relaxed">{proposal.skill_description}</p>
                                {#if proposal.proposal_path}<a href={proposal.proposal_path} target="_blank" rel="noreferrer" class="mt-4 inline-block font-mono text-[10px] font-bold underline">Lihat proposal PDF ↗</a>{/if}
                            </article>
                        {/if}
                    {/each}
                </div>
            {/if}
        </section>
        {/if}
    </div>
</main>