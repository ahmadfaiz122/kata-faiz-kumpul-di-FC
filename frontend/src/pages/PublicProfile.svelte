<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    /** @type {{ params?: { id?: string } }} */
    let { params = {} } = $props();
    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let user = $state(null);
    let loading = $state(true);
    let error = $state("");

    onMount(async () => {
        try {
            const response = await fetch(`${backendUrl}/api/profiles/${params.id}`, { headers: { Accept: "application/json" } });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Profil tidak dapat dimuat.");
            user = result;
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Profil tidak dapat dimuat.";
        } finally {
            loading = false;
        }
    });
</script>

<main class="min-h-screen bg-[#d8d8d8] px-5 py-6 sm:px-10 lg:px-14">
    <header class="mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 sm:h-14 sm:w-36"><img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain" /></a>
        <Navbar />
        <div class="justify-self-end"><ProfileDropdown /></div>
    </header>

    <section class="mx-auto mt-14 max-w-215">
        <button type="button" onclick={() => push("/timeline")} class="mb-6 font-mono text-xs font-bold uppercase">&larr; Kembali ke timeline</button>
        {#if loading}
            <p class="border-2 border-pitch-black bg-off-white p-6 font-mono text-xs">Memuat profil...</p>
        {:else if error}
            <p class="border-2 border-pitch-black bg-[#ffd6df] p-6 font-mono text-xs">{error}</p>
        {:else if user}
            <section class="border-3 border-pitch-black bg-off-white p-6 shadow-[8px_8px_0_#000] sm:p-10">
                <div class="flex flex-wrap items-start gap-6">
                    <div class="h-28 w-28 shrink-0 overflow-hidden border-3 border-pitch-black bg-electric-cyan shadow-[5px_5px_0_#000]">
                        {#if user.avatar}<img src={user.avatar} alt={user.name} class="h-full w-full object-cover" />{:else}<span class="flex h-full items-center justify-center font-anton text-5xl">{user.name?.slice(0, 1)}</span>{/if}
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase text-laser-pink">Profil pengguna</p>
                        <h1 class="mt-2 font-anton text-5xl uppercase leading-none">{user.name}</h1>
                        <p class="mt-3 max-w-130 font-archivo text-sm">{user.profile?.bio || "Belum ada bio."}</p>
                    </div>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="stat-box bg-neon-yellow"><strong>{user.profile?.rating_average ?? "0.00"}/5</strong><span>Rating average</span></div>
                    <div class="stat-box bg-laser-pink text-off-white"><strong>{user.profile?.reputation_score ?? 50}/100</strong><span>Reputation</span></div>
                    <div class="stat-box bg-electric-cyan"><strong>{user.profile?.dominant_reputation_emoji || "-"}</strong><span>Reputasi dominan</span></div>
                </div>
                <div class="mt-8">
                    <h2 class="font-mono text-sm font-bold uppercase">Skill</h2>
                    <div class="mt-3 flex flex-wrap gap-2">{#each user.profile?.skill_records || [] as skill}<span class="border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs font-bold">{skill.name}</span>{/each}</div>
                </div>
            </section>

            <section class="profile-section">
                <div class="section-heading"><h2>Achievement</h2><span>{user.profile?.achievement_records?.length || 0}</span></div>
                {#if user.profile?.achievement_records?.length}
                    <div class="achievement-grid">
                        {#each user.profile.achievement_records as achievement}
                            <article class="achievement-card">
                                <h3>{achievement.name}</h3>
                                {#if achievement.description}<p>{achievement.description}</p>{/if}
                            </article>
                        {/each}
                    </div>
                {:else}<p class="empty-copy">Belum ada achievement yang ditampilkan.</p>{/if}
            </section>

            <section class="profile-section">
                <div class="section-heading"><h2>Post Timeline</h2><span>{user.posts?.length || 0}</span></div>
                {#if user.posts?.length}
                    <div class="post-list">
                        {#each user.posts as post}
                            <article class="public-post"><p>{post.content}</p><time>{new Date(post.created_at).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" })}</time><div class="post-meta">{post.likes_count || 0} like · {post.comments_count || 0} komentar</div></article>
                        {/each}
                    </div>
                {:else}<p class="empty-copy">Belum ada post timeline.</p>{/if}
            </section>

            <section class="profile-section">
                <div class="section-heading"><h2>Post Swapp</h2><span>{user.swapp_posts?.length || 0}</span></div>
                {#if user.swapp_posts?.length}
                    <div class="swapp-grid">
                        {#each user.swapp_posts as proposal}
                            <article class="swapp-card">
                                <div class="swapp-tag">{proposal.skill_name || proposal.skill?.name || "Skill"}</div>
                                <h3>{proposal.skill_category || proposal.skill?.category_skills || "Proposal skill"}</h3>
                                <p>{proposal.skill_description}</p>
                                {#if proposal.available_at}<time>Mulai: {new Date(proposal.available_at).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" })}</time>{/if}
                                <a href={`/#/rekrut/${proposal.id}`} class="view-proposal">Lihat proposal &rarr;</a>
                            </article>
                        {/each}
                    </div>
                {:else}<p class="empty-copy">Belum ada post Swapp aktif.</p>{/if}
            </section>
        {/if}
    </section>
</main>

<style>
    .stat-box { display: grid; gap: 0.35rem; border: 2px solid #000; padding: 1rem; box-shadow: 4px 4px 0 #000; }
    .stat-box strong { font-family: "Anton", sans-serif; font-size: 2rem; line-height: 1; }
    .stat-box span { font-family: "Space Mono", monospace; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
    .profile-section { margin-top: 1.75rem; border: 3px solid #000; background: #fffdf5; padding: 1.25rem; box-shadow: 6px 6px 0 #000; }
    .section-heading { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 0.7rem; }
    .section-heading h2 { font-family: "Anton", sans-serif; font-size: 1.8rem; text-transform: uppercase; }
    .section-heading span { border: 2px solid #000; background: #ffe477; padding: 0.25rem 0.5rem; font-family: "Space Mono", monospace; font-size: 0.65rem; font-weight: 700; }
    .empty-copy { padding-top: 1rem; font-family: "Space Mono", monospace; font-size: 0.7rem; color: #333; }
    .achievement-grid, .swapp-grid { display: grid; gap: 0.8rem; margin-top: 1rem; }
    .achievement-card { border: 2px solid #000; background: #b6ff00; padding: 1rem; box-shadow: 3px 3px 0 #000; }
    .achievement-card h3, .swapp-card h3 { font-family: "Anton", sans-serif; font-size: 1.35rem; text-transform: uppercase; }
    .achievement-card p, .swapp-card p { margin-top: 0.45rem; font-family: "Archivo", sans-serif; font-size: 0.8rem; line-height: 1.45; }
    .post-list { display: grid; gap: 0.8rem; margin-top: 1rem; }
    .public-post { border: 2px solid #000; background: #f1f1f1; padding: 1rem; box-shadow: 3px 3px 0 #000; }
    .public-post p { font-family: "Archivo", sans-serif; font-size: 0.9rem; line-height: 1.5; }
    .public-post time, .swapp-card time { display: block; margin-top: 0.75rem; font-family: "Space Mono", monospace; font-size: 0.62rem; }
    .post-meta { margin-top: 0.45rem; font-family: "Space Mono", monospace; font-size: 0.62rem; color: #444; }
    .swapp-card { border: 2px solid #000; background: #8bd5ff; padding: 1rem; box-shadow: 3px 3px 0 #000; }
    .swapp-tag { display: inline-block; border: 2px solid #000; background: #fff; padding: 0.3rem 0.5rem; font-family: "Space Mono", monospace; font-size: 0.65rem; font-weight: 700; }
    .view-proposal { display: inline-block; margin-top: 0.9rem; border: 2px solid #000; background: #ff006e; color: #fff; padding: 0.55rem 0.7rem; font-family: "Space Mono", monospace; font-size: 0.65rem; font-weight: 700; box-shadow: 3px 3px 0 #000; }
    .view-proposal:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #000; }
</style>
