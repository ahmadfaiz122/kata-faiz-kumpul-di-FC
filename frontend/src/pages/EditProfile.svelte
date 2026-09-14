<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
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
    /** @type {File|null} */
    let skillMaterialFile = null;
    let achievementName = "";
    let achievementDescription = "";
    let achievementOrganization = "";
    let achievementDatetgl = "";
    let achievementDateTahun = "";
    let achievementExpirytgl = "";
    let achievementExpiryTahun = "";
    let achievementLevel = "";
    /** @type {File|null} */
    let certificateFile = null;
    let achievementSubmitting = false;
    let achievementError = "";
    let skillSubmitting = false;
    let skillError = "";
    /** @type {File|null} */
    let avatarFile = null;
    /** @type {HTMLInputElement|undefined} */
    let avatarInput;
    let profile = { name: "", username: "", alias: "", bio: "", email: "", nim: "", photo: "", linkedin: "", github: "", instagram: "", twitter: "" };

    /** @type {Array<{id: number|string, name: string, tanggal_terbit?: number, kadaluwarsa?: number, levels?: string, certificate_path?: string}>} */
    let achievements = [];
    /** @type {Array<{id: number|string, name: string}>} */
    let skills = [];

    onMount(async () => {
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
                linkedin: stored.linkedin || "",
                github: stored.github || "",
                instagram: stored.instagram || "",
                twitter: stored.twitter || "",
            };
            skills = stored.skill_records || [];
            achievements = stored.achievement_records || [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profile.";
        } finally {
            loading = false;
        }
    });

    /** @param {Event & {currentTarget: HTMLInputElement}} event */
    function selectAvatar(event) {
        const file = event.currentTarget.files?.[0];
        if (!file) return;
        if (!file.type.startsWith("image/")) {
            error = "Avatar harus berupa file gambar.";
            event.currentTarget.value = "";
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            error = "Ukuran avatar maksimal 2 MB.";
            event.currentTarget.value = "";
            return;
        }
        avatarFile = file;
        profile = { ...profile, photo: URL.createObjectURL(file) };
        error = "";
    }

    async function saveProfile() {
        saving = true;
        saved = false;
        error = "";
        try {
            const body = new FormData();
            body.append("_method", "PUT");
            body.append("name", profile.name);
            body.append("username", profile.username);
            body.append("bio", profile.bio);
            body.append("email", profile.email);
            body.append("nim", profile.nim);
            for (const [field, value] of [["linkedin", profile.linkedin], ["instagram", profile.instagram], ["github", profile.github]]) {
                const link = value.trim();
                if (!link) continue;
                try {
                    const parsedLink = new URL(link);
                    if (!['http:', 'https:'].includes(parsedLink.protocol)) throw new Error();
                } catch {
                    const label = field === "instagram" ? "Instagram" : field[0].toUpperCase() + field.slice(1);
                    throw new Error(`${label} harus berupa URL lengkap, contoh: https://...`);
                }
                body.append(field, link);
            }
            if (avatarFile) body.append("avatar", avatarFile);

            const response = await fetch(`${backendUrl}/api/profile`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body,
            });
            
            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                push("/login");
                return;
            }
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || "Profile gagal disimpan.");
            }
            push("/profile");
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Profile gagal disimpan.";
        } finally {
            saving = false;
        }
    }

    function closeModal() {
        activeModal = "";
        skillName = "";
        skillCategory = "";
        skillMaterialFile = null;
        achievementName = "";
        achievementDescription = "";
        achievementDateTahun = "";
        achievementDatetgl = "";
        achievementExpiryTahun = "";
        achievementExpirytgl = "";
        achievementLevel = "";
        certificateFile = null;
    }

    /** @param {unknown} requestError */
    function errorMessage(requestError) {
        return requestError instanceof Error ? requestError.message : "Permintaan gagal.";
    }

    async function addSkill() {
        if (!skillName.trim() || !skillCategory || !skillMaterialFile || skillSubmitting) return;
        skillSubmitting = true;
        skillError = "";
        try {
            const body = new FormData();
            body.append("name", skillName.trim());
            body.append("category_skills", skillCategory);
            body.append("material", skillMaterialFile);
            const result = await fetch(`${backendUrl}/api/skills`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body,
            });
            const data = await result.json();
            if (!result.ok) {
                const validationMessage = data.errors ? Object.values(data.errors).flat().join(" ") : data.message;
                throw new Error(validationMessage || "Skill gagal disimpan.");
            }
            skills = [...skills, data.data];
            closeModal();
        } catch (requestError) {
            skillError = errorMessage(requestError);
        } finally {
            skillSubmitting = false;
        }
    }

    async function addAchievement() {
        if (!achievementName.trim() || !certificateFile || achievementSubmitting) return;
        if (!achievementDateTahun || !achievementDatetgl || achievementDatetgl === "Bulan") {
            achievementError = "Tanggal terbit wajib diisi.";
            return;
        }
        if (!achievementExpiryTahun || !achievementExpirytgl || achievementExpirytgl === "Bulan") {
            achievementError = "Tanggal kedaluwarsa wajib diisi.";
            return;
        }
        if (!achievementLevel.trim()) {
            achievementError = "Tingkat prestasi wajib dipilih.";
            return;
        }
        achievementSubmitting = true;
        achievementError = "";
        try {
            const body = new FormData();
            body.append("name", achievementName.trim());
            body.append("description", achievementDescription.trim());
            body.append("tanggal_terbit", String(parseInt(String(achievementDateTahun) + String(achievementDatetgl), 10) || ""));
            body.append("kadaluwarsa", String(parseInt(String(achievementExpiryTahun) + String(achievementExpirytgl), 10) || ""));
            body.append("levels", achievementLevel.trim());
            body.append("certificate", certificateFile);

            const result = await fetch(`${backendUrl}/api/achievements`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body,
                signal: AbortSignal.timeout(30000),
            });
            const data = await result.json();
            if (!result.ok) {
                const validationMessage = data.errors
                    ? Object.values(data.errors).flat().join(" ")
                    : data.message;
                throw new Error(validationMessage || "Prestasi gagal disimpan.");
            }
            achievements = [...achievements, data.data];
            closeModal();
        } catch (requestError) {
            achievementError = errorMessage(requestError);
        } finally {
            achievementSubmitting = false;
        }
    }

    /** @param {number|string} skillId @param {string} skillName */
    async function removeSkill(skillId, skillName) {
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/skills/${skillId}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
            });
            skills = skills.filter((item) => item.name !== skillName);
        } catch (requestError) {
            error = errorMessage(requestError);
        }
    }

    /** @param {number|string} achievementId @param {number} index */
    async function removeAchievement(achievementId, index) {
        try {
            const result = await fetch(`${backendUrl}/api/achievements/${achievementId}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
            });
            const data = await result.json();
            achievements = achievements.filter((_, itemIndex) => itemIndex !== index);
        } catch (requestError) {
            achievementError = errorMessage(requestError);
        }
    }
</script>

<main class="relative min-h-screen overflow-hidden bg-[#d8d8d8] px-5 py-5 sm:px-10 lg:px-18">
    <div class="pointer-events-none absolute -left-24 -top-24 h-52 w-52 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-64 sm:w-64"></div>
    <div class="pointer-events-none absolute -right-11.25 top-0 h-24 w-24 rounded-full border-2 border-pitch-black bg-[#2fc7b8]"></div>
    <div class="relative mx-auto max-w-255">
        <header class="dashboard-enter relative z-30 flex items-center justify-between border-t border-pitch-black pt-5">
            <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"><img src="src/assets/logo.png" alt="Faiz Logo" class="h-full w-full scale-[1.2] object-contain" /></a>
            <Navbar />
            <ProfileDropdown />
        </header>

        {#if loading}
            <p class="py-20 text-center font-mono text-sm">Memuat profile...</p>
        {:else}
        <div class="mt-10">
            <a href="/#/profile" aria-label="Kembali ke profile" title="Kembali ke profile" class="button-lift inline-flex h-11 w-11 items-center justify-center border-2 border-pitch-black bg-off-white text-xl font-bold shadow-[4px_4px_0_#000]">←</a>
        </div>
        <form onsubmit={(event) => { event.preventDefault(); saveProfile(); }} class="pb-10">
            <section class="dashboard-enter dashboard-enter-delay-1 mt-12 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
                <h1 class="font-mono text-xl font-bold sm:text-2xl">General Information</h1>
                <div class="mt-6 grid gap-7 sm:grid-cols-[120px_1fr]">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex h-28 w-28 items-center justify-center border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000]">
                            {#if profile.photo}<img src={profile.photo} alt="Profile preview" class="h-full w-full object-cover" />{/if}
                        </div>
                        <input bind:this={avatarInput} onchange={selectAvatar} type="file" accept="image/*" class="hidden" />
                        <button type="button" onclick={() => avatarInput?.click()} class="button-lift bg-electric-cyan px-3 py-2 font-mono text-[10px] font-bold shadow-[3px_3px_0_#000]" style="--button-complement: #ff006e">Upload Avatar</button>
                        <span class="font-mono text-[8px]">Image only, max 2 MB</span>
                    </div>
                    <div class="grid gap-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-1 font-mono text-[10px]">Full Name<input bind:value={profile.name} class="form-input" /></label>
                            <label class="grid gap-1 font-mono text-[10px]">Username<input bind:value={profile.username} class="form-input" /></label>
                        </div>
                        <label class="grid gap-1 font-mono text-[10px]">About<textarea bind:value={profile.bio} placeholder="Story about yourself..." rows="5" class="form-input resize-none"></textarea></label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-1 font-mono text-[10px]">Email<input type="email" bind:value={profile.email} class="form-input" /></label>
                            <label class="grid gap-1 font-mono text-[10px]">NIM<input value={profile.nim} readonly class="form-input cursor-not-allowed bg-[#e8e8e8]" /></label>
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
                                <div class="flex-1 font-archivo text-[9px]"><p>{achievement.name}</p><p class="mt-2 text-[8px]">Dibuat {String(achievement.tanggal_terbit ?? 0).slice(-2)}/20{Math.floor((achievement.tanggal_terbit ?? 0) / 100)} | Kedaluwarsa {String(achievement.kadaluwarsa ?? 0).slice(-2)}/20{Math.floor((achievement.kadaluwarsa ?? 0) / 100)}</p>{#if achievement.certificate_path}<a href={achievement.certificate_path} target="_blank" rel="noreferrer" class="mt-2 inline-block underline">Lihat sertifikat</a>{/if}</div>
                                <span class="bg-[#ffa174] px-2 py-1 text-[8px]">{achievement.levels}</span>
                                <button type="button" aria-label="Delete achievement" onclick={() => removeAchievement(achievement.id, index)} class="text-sm">♙</button>
                            </div>
                        {/each}
                    </div>
                </div>
                <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[10px] font-bold"><span>Skill dan Kemampuan</span><button type="button" onclick={() => activeModal = "skill"} class="button-lift bg-electric-cyan px-3 py-1 shadow-[2px_2px_0_#000]" style="--button-complement: #ff006e">＋ Tambah</button></div>
                    <div class="mt-2 min-h-20 border-2 border-pitch-black p-3 shadow-[3px_3px_0_#000]"><p class="font-mono text-[9px]">Skills I Can Teach</p>{#each skills as skill}<button type="button" onclick={() => removeSkill(skill.id, skill.name)} class="mr-2 mt-2 inline-block rounded-full border-2 border-pitch-black bg-[#ffa174] px-3 py-1 font-mono text-[9px]">{skill.name} ×</button>{/each}</div>
                </div>
            </section>

            <section class="dashboard-enter dashboard-enter-delay-3 mt-9 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000] sm:p-9">
                <h2 class="font-mono text-xl font-bold sm:text-2xl">Social Media</h2>
                <div class="mt-8 grid gap-6">
                    <label class="grid gap-1 font-mono text-[10px]">LinkedIn Account<input type="text" inputmode="url" placeholder="https://linkedin.com/in/..." bind:value={profile.linkedin} class="form-input" /></label>
                    <label class="grid gap-1 font-mono text-[10px]">Instagram Account<input type="text" inputmode="url" placeholder="https://instagram.com/..." bind:value={profile.instagram} class="form-input" /></label>
                    <label class="grid gap-1 font-mono text-[10px]">Github Account (optional)<input type="text" inputmode="url" placeholder="https://github.com/..." bind:value={profile.github} class="form-input" /></label>
                </div>
            </section>

            <div class="mt-10 flex items-center justify-center gap-4">
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
                    <label class="grid max-w-135 gap-3 font-archivo text-2xl">Kategori<select bind:value={skillCategory} required class="modal-input"><option value="">Pilih Kategori...</option><option>Design</option><option>Technology</option><option>Business</option><option>Language</option></select></label>
                    <label class="grid gap-3 font-archivo text-2xl">Materi PDF<input required type="file" accept="application/pdf,.pdf" onchange={(event) => skillMaterialFile = event.currentTarget.files?.[0] ?? null} class="modal-input" /></label>
                    <p class="font-mono text-xs">Wajib PDF, maksimal 20 MB.</p>
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
                        <label class="grid gap-2 font-archivo text-lg">Deskripsi<textarea bind:value={achievementDescription} maxlength="2000" required placeholder="Ceritakan prestasi ini..." class="modal-input min-h-24"></textarea></label>
                        <label class="grid gap-2 font-archivo text-lg">Organisasi Penerbit<input bind:value={achievementOrganization} placeholder="Nama Organisasi Penerbit..." class="modal-input" /></label>
                        <div class="grid gap-4 sm:grid-cols-2"><label class="grid gap-2 font-archivo text-lg">Tanggal Terbit<select bind:value={achievementDatetgl} class="modal-input"><option>Bulan</option>
                            <option>01</option>
                            <option>02</option>
                            <option>03</option>
                            <option>04</option>
                            <option>05</option>
                            <option>06</option>
                            <option>07</option>
                            <option>08</option>
                            <option>09</option>
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                        </select></label><input bind:value={achievementDateTahun} aria-label="Tahun terbit" class="modal-input self-end" type="number" placeholder="2 Digit Tahun" min="10" max="26"/></div>
                        <div class="grid gap-4 sm:grid-cols-2"><label class="grid gap-2 font-archivo text-lg">Kadaluwarsa<select bind:value={achievementExpirytgl} class="modal-input"><option>Bulan</option>
                            <option>01</option>
                            <option>02</option>
                            <option>03</option>
                            <option>04</option>
                            <option>05</option>
                            <option>06</option>
                            <option>07</option>
                            <option>08</option>
                            <option>09</option>
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                        </select></label><input bind:value={achievementExpiryTahun} aria-label="Tahun Kadaluwarsa" class="modal-input self-end" type="number" placeholder="2 Digit Tahun" min="14" max="99"/></div>
                        <label class="grid gap-2 font-archivo text-lg">Tingkat<select bind:value={achievementLevel} class="modal-input"><option value="">Pilih tingkat</option><option>Nasional</option><option>Internasional</option></select></label>
                        <label class="grid gap-2 font-archivo text-lg">Sertifikat pendukung<input required type="file" accept="image/*,.pdf,application/pdf" onchange={(event) => certificateFile = event.currentTarget.files?.[0] ?? null} class="modal-input" /></label>
                        <p class="font-mono text-[10px]">Wajib diunggah. Format gambar atau PDF, maksimal 10 MB.</p>
                        <div class="flex justify-end">{#if achievementError}<p class="font-archivo text-lg text-[#b3261e]">{achievementError}</p>{/if}</div>
                        <div class="flex justify-end">
            <button type="submit" disabled={achievementSubmitting} class="button-lift bg-[#48b3cf] px-7 py-2 font-archivo text-lg shadow-[4px_4px_0_#000] disabled:opacity-50" style="--button-complement: #ccff00">
                {achievementSubmitting ? "Menyimpan..." : "Validasi"}
            </button>
                    </div>
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
