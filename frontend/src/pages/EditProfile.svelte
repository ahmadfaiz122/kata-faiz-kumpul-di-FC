<script>
    import { onMount } from "svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let saved = false;
    let loading = true;
    let saving = false;
    let error = "";
    let activeModal = "";
    let skillName = "";
    let skillCategory = "";
    let achievementName = "";
    let achievementOrganization = "";
    let profile = { name: "", username: "", alias: "", bio: "", email: "", nim: "", photo: "", linkedin: "", github: "", twitter: "" };

    let achievements = [];
    let skills = [];

    onMount(async () => {
        const token = localStorage.getItem("auth_token");
        if (!token) {
            window.location.href = "/#/login";
            return;
        }

        try {
            const response = await fetch(`${backendUrl}/api/profile`, {
                headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
            });
            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                window.location.href = "/#/login";
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
                linkedin: stored.linkedin || "",
                github: stored.github || "",
                twitter: stored.twitter || "",
            };
            skills = stored.skills || [];
            achievements = stored.achievements || [];
        } catch (requestError) {
            error = requestError.message;
        } finally {
            loading = false;
        }
    });

    async function saveProfile() {
        saving = true;
        saved = false;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/profile`, {
                method: "PUT",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body: JSON.stringify({
                    ...profile,
                    avatar: profile.photo || null,
                    skills,
                    achievements,
                }),
            });
            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                window.location.href = "/#/login";
                return;
            }
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || "Profile gagal disimpan.");
            }
            saved = true;
        } catch (requestError) {
            error = requestError.message;
        } finally {
            saving = false;
        }
    }

    function closeModal() {
        activeModal = "";
        skillName = "";
        skillCategory = "";
        achievementName = "";
        achievementOrganization = "";
    }

    function addSkill() {
        if (skillName.trim()) {
            skills = [...skills, skillName.trim()];
            closeModal();
        }
    }

    function addAchievement() {
        if (achievementName.trim()) {
            achievements = [...achievements, achievementName.trim()];
            closeModal();
        }
    }

    function removeSkill(skill) {
        skills = skills.filter((item) => item !== skill);
    }

    function removeAchievement(index) {
        achievements = achievements.filter((_, itemIndex) => itemIndex !== index);
    }
</script>

<main class="relative min-h-screen overflow-hidden bg-[#d8d8d8] px-5 py-5 sm:px-10 lg:px-18">
    <div class="pointer-events-none absolute -left-24 -top-24 h-52 w-52 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-64 sm:w-64"></div>
    <div class="pointer-events-none absolute right-[-45px] top-0 h-24 w-24 rounded-full border-2 border-pitch-black bg-[#2fc7b8]"></div>
    <div class="relative mx-auto max-w-255">
        <header class="dashboard-enter relative z-30 flex items-center justify-between border-t border-pitch-black pt-5">
            <a href="/#/" aria-label="Faiz home" class="h-9 w-20 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] sm:h-11 sm:w-28"></a>
            <Navbar />
            <ProfileDropdown />
        </header>

        {#if loading}
            <p class="py-20 text-center font-mono text-sm">Memuat profile...</p>
        {:else}
        <form onsubmit={(event) => { event.preventDefault(); saveProfile(); }} class="pb-10">
            <section class="dashboard-enter dashboard-enter-delay-1 mt-12 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
                <h1 class="font-mono text-xl font-bold sm:text-2xl">General Information</h1>
                <div class="mt-6 grid gap-7 sm:grid-cols-[120px_1fr]">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex h-28 w-28 items-center justify-center border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000]">
                            {#if profile.photo}<img src={profile.photo} alt="Profile preview" class="h-full w-full object-cover" />{/if}
                        </div>
                        <button type="button" class="button-lift bg-electric-cyan px-3 py-2 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000]" style="--button-complement: #ff006e">Upload Avatar</button>
                        <span class="font-mono text-[8px]">Max size 1MB</span>
                    </div>
                    <div class="grid gap-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-1 font-mono text-[10px]">Full Name<input bind:value={profile.name} class="form-input" /></label>
                            <label class="grid gap-1 font-mono text-[10px]">Username<input bind:value={profile.username} class="form-input" /></label>
                        </div>
                        <label class="grid gap-1 font-mono text-[10px]">About<textarea bind:value={profile.bio} placeholder="Story about yourself..." rows="5" class="form-input resize-none"></textarea></label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-1 font-mono text-[10px]">Email<input type="email" bind:value={profile.email} class="form-input" /></label>
                            <label class="grid gap-1 font-mono text-[10px]">NIM<input bind:value={profile.nim} class="form-input" /></label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-enter dashboard-enter-delay-2 mt-9 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
                <h2 class="font-mono text-xl font-bold sm:text-2xl">Achievement</h2>
                <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[10px] font-bold"><span>Sertifikasi dan Prestasi</span><button type="button" onclick={() => activeModal = "achievement"} class="button-lift bg-electric-cyan px-3 py-1 shadow-[2px_2px_0_#000]" style="--button-complement: #ff006e">＋ Tambah</button></div>
                    <div class="mt-2 border-2 border-pitch-black p-3 shadow-[3px_3px_0_#000]">
                        {#each achievements as achievement, index}
                            <div class="flex items-center gap-3 {index > 0 ? 'border-t border-pitch-black/50 pt-3 mt-3' : ''}">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center border-2 border-pitch-black bg-[#ffe477] text-xl">🏅</div>
                                <div class="flex-1 font-archivo text-[9px]"><p>{achievement}</p><p class="mt-2 text-[8px]">Dibuat 09/2026 | Kedaluwarsa 03/2030</p></div>
                                <span class="bg-[#ffa174] px-2 py-1 text-[8px]">INTERNASIONAL</span>
                                <button type="button" aria-label="Delete achievement" onclick={() => removeAchievement(index)} class="text-sm">♙</button>
                            </div>
                        {/each}
                    </div>
                </div>
                <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[10px] font-bold"><span>Skill dan Kemampuan</span><button type="button" onclick={() => activeModal = "skill"} class="button-lift bg-electric-cyan px-3 py-1 shadow-[2px_2px_0_#000]" style="--button-complement: #ff006e">＋ Tambah</button></div>
                    <div class="mt-2 min-h-20 border-2 border-pitch-black p-3 shadow-[3px_3px_0_#000]"><p class="font-mono text-[9px]">Skills I Can Teach</p>{#each skills as skill}<button type="button" onclick={() => removeSkill(skill)} class="mr-2 mt-2 inline-block rounded-full border-2 border-pitch-black bg-[#ffa174] px-3 py-1 font-mono text-[9px]">{skill} ×</button>{/each}</div>
                </div>
            </section>

            <section class="dashboard-enter dashboard-enter-delay-3 mt-9 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
                <h2 class="font-mono text-xl font-bold sm:text-2xl">Social Media</h2>
                <div class="mt-8 grid gap-6">
                    <label class="grid gap-1 font-mono text-[10px]">LinkedIn Account<input bind:value={profile.linkedin} class="form-input" /></label>
                    <label class="grid gap-1 font-mono text-[10px]">Github Account<input bind:value={profile.github} class="form-input" /></label>
                    <label class="grid gap-1 font-mono text-[10px]">X Account<input bind:value={profile.twitter} class="form-input" /></label>
                </div>
            </section>

            <div class="mt-10 flex items-center justify-center gap-4">
                {#if saved}<span class="font-mono text-xs font-bold text-[#168f56]">Saved!</span>{/if}
                {#if error}<span class="font-mono text-xs font-bold text-[#b3261e]">{error}</span>{/if}
                <button type="submit" disabled={saving} class="button-lift bg-pale-purple px-6 py-3 font-mono text-xs font-bold shadow-[4px_4px_0_#000] disabled:opacity-50" style="--button-complement: #ff006e">{saving ? "SAVING..." : "SAVE CHANGES"}</button>
            </div>
        </form>
        {/if}
    </div>
</main>

{#if activeModal}
    <div class="fixed inset-0 z-200 flex items-center justify-center bg-pitch-black/45 p-4" role="presentation" onclick={(event) => event.target === event.currentTarget && closeModal()}>
        {#if activeModal === "skill"}
            <dialog open aria-labelledby="skill-modal-title" class="dashboard-enter w-full max-w-175 overflow-hidden rounded-xl border-4 border-pitch-black bg-off-white shadow-[10px_10px_0_#000]">
                <header class="flex items-center justify-between border-b-4 border-pitch-black bg-[#ffe477] px-6 py-4 sm:px-9"><h2 id="skill-modal-title" class="font-archivo text-3xl font-bold sm:text-4xl">Tambahkan Skill</h2><button type="button" onclick={closeModal} aria-label="Close skill modal" class="text-5xl leading-none text-[#8d7927]">×</button></header>
                <form onsubmit={(event) => { event.preventDefault(); addSkill(); }} class="space-y-10 px-8 py-10 sm:px-20 sm:py-12">
                    <label class="grid gap-3 font-archivo text-2xl">Nama Skill<input bind:value={skillName} required placeholder="eg. Figma.." class="modal-input" /></label>
                    <label class="grid max-w-135 gap-3 font-archivo text-2xl">Kategori<select bind:value={skillCategory} class="modal-input"><option value="">Pilih Kategori...</option><option>Design</option><option>Technology</option><option>Business</option><option>Language</option></select></label>
                    <div class="flex justify-end"><button type="submit" class="button-lift bg-[#48b3cf] px-7 py-3 font-archivo text-2xl shadow-[5px_5px_0_#000]" style="--button-complement: #ff006e">Tambahkan</button></div>
                </form>
            </dialog>
        {:else}
            <dialog open aria-labelledby="achievement-modal-title" class="dashboard-enter w-full max-w-190 overflow-hidden border-4 border-pitch-black bg-off-white p-0 shadow-[10px_10px_0_#000]">
                <header class="flex items-center justify-between border-b-4 border-pitch-black bg-laser-pink px-6 py-4 sm:px-9"><h2 id="achievement-modal-title" class="font-archivo text-2xl font-bold sm:text-3xl">Tambahkan Prestasi</h2><button type="button" onclick={closeModal} aria-label="Close achievement modal" class="text-5xl leading-none">×</button></header>
                <form onsubmit={(event) => { event.preventDefault(); addAchievement(); }} class="grid gap-7 px-8 py-10 sm:grid-cols-[130px_1fr] sm:px-16 sm:py-14">
                    <div class="flex h-28 w-28 items-center justify-center border-2 border-pitch-black bg-[#ffe477] text-5xl shadow-[5px_5px_0_#000]">🏅</div>
                    <div class="grid gap-6">
                        <label class="grid gap-2 font-archivo text-lg">Nama Prestasi<input bind:value={achievementName} required placeholder="Masukkan Nama Prestasi.." class="modal-input" /></label>
                        <label class="grid gap-2 font-archivo text-lg">Organisasi Penerbit<input bind:value={achievementOrganization} placeholder="Nama Organisasi Penerbit..." class="modal-input" /></label>
                        <div class="grid gap-4 sm:grid-cols-2"><label class="grid gap-2 font-archivo text-lg">Tanggal Terbit<select class="modal-input"><option>Bulan</option><option>09</option></select></label><select aria-label="Tahun terbit" class="modal-input self-end"><option>Tahun</option><option>2026</option></select></div>
                        <label class="grid gap-2 font-archivo text-lg">Tingkat<select class="modal-input"><option>Nasional</option><option>Internasional</option></select></label>
                        <div class="flex justify-end"><button type="submit" class="button-lift bg-[#48b3cf] px-7 py-2 font-archivo text-lg shadow-[4px_4px_0_#000]" style="--button-complement: #ccff00">Validasi</button></div>
                    </div>
                </form>
            </dialog>
        {/if}
    </div>
{/if}

<style>
    :global(.form-input) {
        width: 100%;
        border: 2px solid #000;
        border-radius: 6px;
        background: #fffdf5;
        padding: 8px 10px;
        font-family: "Archivo", sans-serif;
        font-size: 11px;
        outline: none;
        box-shadow: 3px 3px 0 #000;
    }

    :global(.form-input:focus) {
        background: #ffe477;
    }

    :global(.modal-input) {
        width: 100%;
        border: 3px solid #202020;
        border-radius: 12px;
        background: #fffdf5;
        padding: 14px 18px;
        font-family: "Archivo", sans-serif;
        font-size: 20px;
        outline: none;
        box-shadow: 6px 6px 0 #202020;
    }

    :global(.modal-input:focus) {
        background: #ffe477;
    }
</style>
