<script>
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import Index from "../lib/profileComponents/Index.svelte";
    import indexImage1 from "../assets/star.webp";
    import indexImage2 from "../assets/stats.webp";
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";

    let profile = { name: "", username: "", alias: "", bio: "", email: "", nim: "", photo: "" };
    let proposal = {}
    let prestasi = []

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let error = "";
    let loading = true

    /** @type {Array<{id: number|string, name: string, tanggal_terbit?: number, kadaluwarsa?: number, levels?: string, certificate_path?: string}>} */
    let achievements = [];
    /** @type {Array<{id: number|string, name: string}>} */
    let skills = [];

    onMount(async () => {
        const storedProposal = sessionStorage.getItem("selected_proposal");
        if (storedProposal) {
            try {
                proposal = JSON.parse(storedProposal);
            } catch {
                sessionStorage.removeItem("selected_proposal");
            }
        }

        const token = localStorage.getItem("auth_token");
        if (!token) {
            push("/login");
            return;
        }

        try {
            const response = await fetch(`${backendUrl}/api/profile`, {
                headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
            });
            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                push("/login");
                return;
            }
            if (!response.ok) throw new Error("Gagal mengambil data profile.");
            const data = await response.json();
            const stored = data.profile || {};
            profile = {
                name: data.name || "",
                email: data.email || "",
                photo: data.avatar || "",
                username: stored.username || "",
                alias: stored.alias || "",
                bio: stored.bio || "",
                nim: stored.nim || "",
            };
            skills = stored.skill_records || [];
            achievements = stored.achievement_records || [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profile.";
        } finally {
            loading = false;
        }
    });
</script>

<main class="relative min-h-screen overflow-hidden bg-[#d8d8d8] px-5 py-5 sm:px-10 lg:px-18">
    <div class="pointer-events-none absolute -left-24 -top-24 h-52 w-52 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-64 sm:w-64"></div>
    <div class="pointer-events-none absolute -right-11.25 top-0 h-24 w-24 rounded-full border-2 border-pitch-black bg-[#2fc7b8]"></div>
    <div class="relative mx-auto max-w-255">
        <header class="dashboard-enter relative z-30 flex items-center justify-between border-t border-pitch-black pt-5">
            <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"><img src="src/assets/logo.webp" alt="Faiz Logo" class="h-full w-full scale-[1.2] object-contain" /></a>
            <Navbar />
            <ProfileDropdown />
        </header>
        <a href="/#/swapp" class="mb-6 mt-12 inline-flex items-center gap-2 font-mono text-xs uppercase transition-transform hover:-translate-x-1">
            <span aria-hidden="true" class="text-lg">&larr;</span>
            Kembali ke barter
        </a>
        <section class="dashboard-enter dashboard-enter-delay-1 mt-4 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
        <h1 class="font-mono text-xl font-bold sm:text-2xl">Informasi Pengajar</h1>
        <div class="mt-6 grid gap-7 sm:grid-cols-[120px_1fr]">
            <div class="flex flex-col items-center gap-3">
                <div class="flex h-28 w-28 items-center justify-center border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000]">
                {#if proposal.requester_user?.avatar || profile.photo}<img src={proposal.requester_user?.avatar || profile.photo} alt="Profile preview" class="h-full w-full object-cover" />{/if}
                </div>
           </div>
           <div class="ml-5 sm:ml-7">
                <h1 class="max-w-140 font-anton leading-none md:text-5xl text-2xl">{proposal.full_name || proposal.requester_user?.name || profile.name}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-3 font-archivo text-xs sm:text-sm">
                    <p>{proposal.email || profile.email}</p>
                </div>
                <div class="bottom-[2px] font-mono left-[4px] flex items-center gap-[6px] mt-2">
                    <svg viewBox="0 0 24 24" width=15px height=15px fill="none">
                        <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.4" stroke-linecap="round" />
                        <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>{proposal.city || "Domisili belum tersedia"}</span>
                </div>
            </div>
        </div>
        <div class='mt-12'>
            <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[13px] font-bold"><span>Deskripsi Skill</span>
                        <button type="button" onclick={() => proposal.id && window.open(`${backendUrl}/api/proposals/${proposal.id}/file`, "_blank")} class=" border-2 border-pitch-black flex button-lift bg-electric-cyan px-3 py-2 shadow-[2px_2px_0_#000]" style="--button-complement: #ff006e">
                            <svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="download-alt" class="icon glyph">
                                <path d="M11.29,16.71h0a1.15,1.15,0,0,0,.33.21.94.94,0,0,0,.76,0,1.15,1.15,0,0,0,.33-.21h0l4-4a1,1,0,0,0-1.42-1.42L13,13.59V3a1,1,0,0,0-2,0V13.59l-2.29-2.3a1,1,0,1,0-1.42,1.42Z"/>
                                <path d="M19,20H5a1,1,0,0,0,0,2H19a1,1,0,0,0,0-2Z"/></svg><p class="ms-1">Download PDF</p></button></div>
                    <div class="mt-2 min-h-20 border-2 border-pitch-black p-3 shadow-[3px_3px_0_#000]">
                                <p class="font-mono text-[15px] leading-relaxed sm:text-xs">{proposal.skill_description || "Deskripsi skill belum tersedia."}</p>
                    </div>
                </div>
        </div>
        <div class='mt-8'>
            <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[13px] font-bold">
                        <span>Detail Prestasi</span>
                    </div>
                            <div class="mt-3 flex flex-col gap-6">
                            {#if achievements.length > 0}
                                {#each achievements as achievement, i}
                                    {@const accentClasses = [
                                        "bg-[#b6ff00]",
                                        "bg-[#2fc7e8]",
                                        "bg-[#ff006e]"
                                    ]}
                                    {@const yearClasses = [
                                        "bg-[#ffe477]",
                                        "bg-[#2fc7e8]",
                                        "bg-[#ffe477]"
                                    ]}
                                    {@const LevelClasses = [
                                        "bg-[#2fc7b8]",
                                        "bg-[#ffe477]",
                                        "bg-[#ffe477]",
                                    ]}
                                    {@const accent = accentClasses[i % accentClasses.length]}
                                    {@const yearAccent = yearClasses[i % yearClasses.length]}
                                    {@const levelAccent = LevelClasses[i % LevelClasses.length]}
                                    {@const issuedYear = achievement.tanggal_terbit
                                        ? "20" + String(achievement.tanggal_terbit).slice(0, 2)
                                        : "----"}
                                    {@const description =
                                        achievement.description ||
                                        "Informasi prestasi belum tersedia. Pastikan bahwa frontend ini terconnect ke backend dengan benar"}

                                    <article class="border-2 border-pitch-black bg-off-white p-4 shadow-[5px_5px_0_#000] sm:p-5">
                                        <div class="flex items-center gap-4">
                                            <div class={`flex h-20 w-20 shrink-0 items-center justify-center border-2 border-pitch-black ${accent} shadow-[4px_4px_0_#000] sm:h-22 sm:w-22`}>
                                                <span class="font-anton text-3xl leading-none sm:text-4xl">
                                                    {String(i + 1).padStart(2, "0")}
                                                </span>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                                                    <div class="min-w-0">
                                                        <h3 class="font-anton text-2xl leading-none sm:text-3xl">
                                                            {achievement.name || "Prestasi"}
                                                        </h3>
                                                        <p class="mt-2 max-w-100 font-mono text-[11px] leading-relaxed sm:text-xs">
                                                            {description}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <span class={`shrink-0 self-start border-2 me-3 border-pitch-black ${levelAccent} px-3 py-1 font-mono text-[11px] font-bold shadow-[3px_3px_0_#000]`}>
                                                            Nasional
                                                        </span>
                                                        <span class={`shrink-0 self-start border-2 border-pitch-black ${yearAccent} px-3 py-1 font-mono text-[11px] font-bold shadow-[3px_3px_0_#000]`}>
                                                            {issuedYear}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                {/each}
                            {:else}
                                <div class="border-2 border-pitch-black bg-off-white p-5 shadow-[5px_5px_0_#000]">
                                    <p class="font-mono text-[11px]">
                                        Belum ada prestasi yang terdaftar.
                                    </p>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                <div>

                </div>
                
        <div>
            <div class="grid gap-10 sm:grid-cols-2 justify-center mt-15">
                <Index photo={indexImage1} index="4.5/5.0" title="Rating" color="neon-yellow" />
                <Index photo={indexImage2} index="100" title="Reputation" color="laser-pink" />
            </div>
        </div>
    </section>
    <div class="flex justify-center">
        <div class="mt-10 flex items-center justify-center gap-4">
            <button type="submit" class="button-lift border-2 border-pitch-black inline-flex items-center justify-center gap-2 bg-white px-6 py-3 font-mono text-sm font-bold shadow-[4px_4px_0_#000] disabled:opacity-50" style="--button-complement: #ff006e">
                <span>Konfirmasi Rekrut</span>
                <svg viewBox="0 0 24 24" class="w-[22px] h-[22px]" fill="none">
                    <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.6" stroke-linecap="round" />
                    <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>
    </div>
</main>