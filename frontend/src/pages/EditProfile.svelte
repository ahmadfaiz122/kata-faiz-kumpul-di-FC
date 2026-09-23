<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import logo from "../assets/logo.webp";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import { alertError, alertSuccess, confirmAction } from "../lib/alerts.js";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let saved = false;
    let loading = $state(true);
    let saving = $state(false);
    let error = $state("");
    let activeModal = $state("");
    let skillName = $state("");
    let skillCategory = $state("");
    let skillCategories = $state([]);
    const skillSuggestions = {
        Technology: ["Frontend Development", "Backend Development", "QA Analyst", "DevOps", "Data Engineering"],
        Education: ["Mathematics", "Academic Writing", "Public Speaking"],
        Business: ["Digital Marketing", "Accounting", "Project Management"],
        Language: ["English Conversation", "Japanese", "Translation"],
        Art: ["Illustration", "Graphic Design", "Photography"],
        Writing: ["Copywriting", "Creative Writing", "Technical Writing"],
    };
    /** @type {File|null} */
    let skillMaterialFile = $state(null);
    let achievementName = $state("");
    let achievementDescription = $state("");
    let achievementOrganization = $state("");
    let achievementIssuedDate = $state("");
    let achievementExpiryDate = $state("");
    let achievementLevel = $state("");
    /** @type {File|null} */
    let certificateFile = $state(null);
    let achievementSubmitting = $state(false);
    let achievementError = $state("");
    let skillSubmitting = $state(false);
    let skillError = $state("");
    /** @type {number|string|null} */
    let editingSkillId = $state(null);
    /** @type {number|string|null} */
    let editingAchievementId = $state(null);
    /** @type {File|null} */
    let avatarFile = $state(null);
    /** @type {HTMLInputElement|undefined} */
    let avatarInput = $state();
    let profile = $state({ name: "", username: "", alias: "", bio: "", email: "", nim: "", photo: "", linkedin: "", github: "", instagram: "", twitter: "" });

    /** @type {Array<{id: number|string, name: string, tanggal_terbit?: number, kadaluwarsa?: number, levels?: string, certificate_path?: string}>} */
    let achievements = $state([]);
    /** @type {Array<{id: number|string, name: string}>} */
    let skills = $state([]);

    onMount(async () => {
        const token = localStorage.getItem("auth_token");
        if (!token) {
            push("/login");
            return;
        }

        try {
            const headers = { Accept: "application/json", Authorization: `Bearer ${token}` };
            const requestOptions = { headers, signal: AbortSignal.timeout(10000) };
            const [response, categoryResponse] = await Promise.all([
                fetch(`${backendUrl}/api/profile`, requestOptions),
                fetch(`${backendUrl}/api/categories`, requestOptions),
            ]);

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
            if (categoryResponse.ok) skillCategories = (await categoryResponse.json()).data || [];
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
            await alertSuccess("Profile tersimpan", "Perubahan profile berhasil disimpan.");
            push("/profile");
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Profile gagal disimpan.";
        } finally {
            saving = false;
        }
    }

    function closeModal() {
        activeModal = "";
        editingSkillId = null;
        editingAchievementId = null;
        skillName = "";
        skillCategory = "";
        skillMaterialFile = null;
        achievementName = "";
        achievementDescription = "";
        achievementIssuedDate = "";
        achievementExpiryDate = "";
        achievementLevel = "";
        certificateFile = null;
        skillError = "";
        achievementError = "";
    }

    /** @param {unknown} requestError */
    function errorMessage(requestError) {
        return requestError instanceof Error ? requestError.message : "Permintaan gagal.";
    }

    function formatDate(value) {
        if (!value) return "-";
        return new Intl.DateTimeFormat("id-ID", { day: "2-digit", month: "2-digit", year: "numeric" }).format(new Date(value));
    }

    async function validatePdfFile(file, fieldLabel) {
        if (file.size > 5 * 1024 * 1024) throw new Error(`${fieldLabel} maksimal 5 MB.`);
        if (!/\.pdf$/i.test(file.name)) throw new Error(`${fieldLabel} harus berformat PDF.`);
        const bytes = new Uint8Array(await file.slice(0, 8).arrayBuffer());
        const header = new TextDecoder().decode(bytes.slice(0, 5));
        if (header !== "%PDF-") throw new Error(`${fieldLabel} bukan PDF asli.`);
        return file;
    }

    async function selectSkillMaterial(event) {
        const file = event.currentTarget.files?.[0];
        skillMaterialFile = null;
        if (!file) return;
        try {
            skillMaterialFile = await validatePdfFile(file, "Materi PDF");
            skillError = "";
        } catch (validationError) {
            event.currentTarget.value = "";
            skillError = errorMessage(validationError);
        }
    }

    async function validateCertificateFile(file) {
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) throw new Error("Ukuran sertifikat maksimal 5 MB.");
        if (!/\.(pdf|jpe?g|png)$/i.test(file.name)) throw new Error("Format sertifikat harus PDF, JPG, JPEG, atau PNG.");

        const bytes = new Uint8Array(await file.slice(0, 8).arrayBuffer());
        const signature = Array.from(bytes).map((byte) => byte.toString(16).padStart(2, "0")).join("");
        const isPdf = new TextDecoder().decode(bytes.slice(0, 5)) === "%PDF-";
        const isJpeg = signature.startsWith("ffd8ff");
        const isPng = signature === "89504e470d0a1a0a";
        if (!isPdf && !isJpeg && !isPng) throw new Error("Isi file tidak sesuai dengan tipe sertifikat.");

        if (!isPdf) {
            const imageUrl = URL.createObjectURL(file);
            try {
                const dimensions = await new Promise((resolve, reject) => {
                    const image = new Image();
                    image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight });
                    image.onerror = () => reject(new Error("Gambar tidak dapat dibaca."));
                    image.src = imageUrl;
                });
                if (dimensions.width < 100 || dimensions.height < 100) throw new Error("Dimensi gambar minimal 100 x 100 piksel.");
            } finally {
                URL.revokeObjectURL(imageUrl);
            }
        }
        return file;
    }

    async function selectCertificate(event) {
        const file = event.currentTarget.files?.[0];
        certificateFile = null;
        if (!file) return;
        try {
            certificateFile = await validateCertificateFile(file);
            achievementError = "";
        } catch (validationError) {
            event.currentTarget.value = "";
            achievementError = errorMessage(validationError);
        }
    }

    /** @param {Array<{id: number|string, name: string, category_skills?: string, description?: string}>} items */
    function openSkillModal(items, skillId) {
        const skill = items.find((item) => String(item.id) === String(skillId));
        if (!skill) return;
        editingSkillId = skill.id;
        skillName = skill.name;
        skillCategory = String(skill.category_id || skillCategories.find((category) => category.name === skill.category_skills)?.id || "");
        skillError = "";
        activeModal = "skill";
    }

    /** @param {{id: number|string, name: string, description?: string, tanggal_terbit?: number, kadaluwarsa?: number, levels?: string}} achievement */
    function openAchievementModal(achievement) {
        editingAchievementId = achievement.id;
        achievementName = achievement.name || "";
        achievementDescription = achievement.description || "";
        achievementLevel = achievement.levels || "";
        achievementIssuedDate = achievement.tanggal_terbit || "";
        achievementExpiryDate = achievement.kadaluwarsa || "";
        certificateFile = null;
        achievementError = "";
        activeModal = "achievement";
    }

    async function addSkill() {
        if (!skillName.trim() || !skillCategory || (!editingSkillId && !skillMaterialFile) || skillSubmitting) return;
        if (skillName.trim().length > 100) {
            skillError = "Nama skill maksimal 100 karakter.";
            return;
        }
        skillSubmitting = true;
        skillError = "";
        try {
            const body = new FormData();
            body.append("name", skillName.trim());
            body.append("category_id", skillCategory);
            if (editingSkillId) body.append("id", String(editingSkillId));
            if (skillMaterialFile) body.append("material", skillMaterialFile);
            const result = await fetch(`${backendUrl}/api/skills`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body,
            });
            const data = await result.json().catch(() => ({}));
            if (!result.ok) {
                const validationMessage = data.errors ? Object.values(data.errors).flat().join(" ") : data.message;
                throw new Error(validationMessage || "Skill gagal disimpan.");
            }
            skills = editingSkillId
                ? skills.map((item) => String(item.id) === String(data.data.id) ? data.data : item)
                : [...skills, data.data];
            closeModal();
        } catch (requestError) {
            skillError = errorMessage(requestError);
        } finally {
            skillSubmitting = false;
        }
    }

    async function addAchievement() {
        if (!achievementName.trim() || (!editingAchievementId && !certificateFile) || achievementSubmitting) return;
        if (!achievementIssuedDate) {
            achievementError = "Tanggal terbit wajib diisi.";
            return;
        }
        if (!achievementExpiryDate) {
            achievementError = "Tanggal kedaluwarsa wajib diisi.";
            return;
        }
        if (achievementExpiryDate < achievementIssuedDate) {
            achievementError = "Tanggal kedaluwarsa harus setelah tanggal terbit.";
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
            if (editingAchievementId) body.append("id", String(editingAchievementId));
            body.append("description", achievementDescription.trim());
            body.append("tanggal_terbit", achievementIssuedDate);
            body.append("kadaluwarsa", achievementExpiryDate);
            body.append("levels", achievementLevel.trim());
            if (certificateFile) body.append("certificate", certificateFile);

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
            achievements = editingAchievementId
                ? achievements.map((item) => String(item.id) === String(data.data.id) ? data.data : item)
                : [...achievements, data.data];
            await alertSuccess("Prestasi tersimpan", "Sertifikat berhasil divalidasi dan disimpan.");
            closeModal();
        } catch (requestError) {
            achievementError = errorMessage(requestError);
            await alertError("Prestasi gagal disimpan", achievementError);
        } finally {
            achievementSubmitting = false;
        }
    }

    /** @param {number|string} skillId @param {string} skillName */
    async function removeSkill(skillId, skillName) {
        const confirmation = await confirmAction(`Hapus skill "${skillName}"?`, "Data ini tidak dapat dikembalikan.");
        if (!confirmation.isConfirmed) return;
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
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || "Skill gagal dihapus.");
            }
            skills = skills.filter((item) => String(item.id) !== String(skillId));
            await alertSuccess("Skill dihapus");
        } catch (requestError) {
            error = errorMessage(requestError);
            await alertError("Skill gagal dihapus", error);
        }
    }

    /** @param {number|string} achievementId @param {string} achievementName */
    async function removeAchievement(achievementId, achievementName) {
        const confirmation = await confirmAction(`Hapus sertifikasi "${achievementName}"?`, "Data ini tidak dapat dikembalikan.");
        if (!confirmation.isConfirmed) return;
        try {
            const result = await fetch(`${backendUrl}/api/achievements/${achievementId}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
            });
            const data = await result.json().catch(() => ({}));
            if (!result.ok) throw new Error(data.message || "Sertifikasi gagal dihapus.");
            achievements = achievements.filter((item) => String(item.id) !== String(achievementId));
            await alertSuccess("Sertifikasi dihapus");
        } catch (requestError) {
            achievementError = errorMessage(requestError);
            await alertError("Sertifikasi gagal dihapus", achievementError);
        }
    }
</script>

<main class="relative min-h-screen overflow-hidden bg-[#d8d8d8] px-5 py-5 sm:px-10 lg:px-18">
    <div class="pointer-events-none absolute -left-24 -top-24 h-52 w-52 rounded-full border-2 border-pitch-black bg-[#ffe477] sm:h-64 sm:w-64"></div>
    <div class="pointer-events-none absolute -right-11.25 top-0 h-24 w-24 rounded-full border-2 border-pitch-black bg-[#2fc7b8]"></div>
    <div class="relative mx-auto max-w-255">
        <header class="dashboard-enter relative z-30 flex items-center justify-between border-t border-pitch-black pt-5">
            <a href="/#/" aria-label="Faiz home" class="h-9 w-20 min-w-0 transition-transform hover:-translate-y-1 sm:h-11 sm:w-28 md:h-14 md:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class=" justify-self-end">
            <ProfileDropdown />
        </div>
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
                            <label class="grid gap-1 font-mono text-[10px]">NIM<input value={profile.nim} readonly placeholder="Diambil dari email kampus" class="form-input cursor-not-allowed bg-[#e8e8e8]" /></label>
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
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 {index > 0 ? 'border-t border-pitch-black/50 pt-3 mt-3' : ''}">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center border-2 border-pitch-black bg-[#ffe477] text-xl">🏅</div>
                                <div class="flex-1 font-archivo text-[9px]"><p>{achievement.name}</p><p class="mt-2 text-[8px]">Terbit {formatDate(achievement.tanggal_terbit)} | Kadaluwarsa {formatDate(achievement.kadaluwarsa)}</p>{#if achievement.certificate_path}<a href={achievement.certificate_path} target="_blank" rel="noreferrer" class="mt-2 inline-block underline">Lihat sertifikat</a>{/if}</div>
                                <span class="bg-[#ffa174] px-2 py-1 text-[8px]">{achievement.levels}</span>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button type="button" aria-label="Edit achievement" onclick={() => openAchievementModal(achievement)} class="border-2 border-pitch-black bg-[#ffe477] px-2 py-1 text-[9px] font-bold shadow-[2px_2px_0_#000]">Edit</button>
                                    <button type="button" aria-label="Delete achievement" onclick={() => removeAchievement(achievement.id, achievement.name)} class="border-2 border-pitch-black bg-laser-pink px-2 py-1 text-[9px] font-bold shadow-[2px_2px_0_#000]">Hapus</button>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
                <div class="mt-8">
                    <div class="flex items-center justify-between font-mono text-[10px] font-bold"><span>Skill dan Kemampuan</span><button type="button" onclick={() => activeModal = "skill"} class="button-lift bg-electric-cyan px-3 py-1 shadow-[2px_2px_0_#000]" style="--button-complement: #ff006e">＋ Tambah</button></div>
                    <div class="mt-2 min-h-20 border-2 border-pitch-black p-3 shadow-[3px_3px_0_#000]"><p class="font-mono text-[9px]">Skills I Can Teach</p>{#each skills as skill}<span class="mr-2 mt-2 inline-flex items-center gap-2 rounded-full border-2 border-pitch-black bg-[#ffa174] px-3 py-1 font-mono text-[9px]"><span>{skill.name}</span><button type="button" aria-label={`Edit ${skill.name}`} onclick={() => openSkillModal(skills, skill.id)} class="font-bold">Edit</button><button type="button" aria-label={`Delete ${skill.name}`} onclick={() => removeSkill(skill.id, skill.name)} class="font-bold">×</button></span>{/each}</div>
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
            <dialog open aria-labelledby="skill-modal-title" class="dashboard-enter relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border-4 border-pitch-black bg-off-white shadow-[10px_10px_0_#000]">
                <header class="sticky top-0 z-10 flex items-center justify-between border-b-4 border-pitch-black bg-[#ffe477] px-5 py-3 sm:px-7"><h2 id="skill-modal-title" class="font-archivo text-xl font-bold sm:text-2xl">{editingSkillId ? "Edit Skill" : "Tambahkan Skill"}</h2><button type="button" onclick={closeModal} aria-label="Close skill modal" class="text-4xl leading-none text-[#8d7927]">×</button></header>
                <form onsubmit={(event) => { event.preventDefault(); addSkill(); }} class="space-y-5 px-5 py-6 sm:px-8 sm:py-8">
                    <label class="grid gap-2 font-archivo text-base sm:text-lg">Nama Skill<input bind:value={skillName} maxlength="100" list="skill-suggestions" required placeholder="eg. Frontend Development.." class="modal-input" /><datalist id="skill-suggestions">{#each skillSuggestions[skillCategories.find((category) => String(category.id) === String(skillCategory))?.name] ?? [] as suggestion}<option value={suggestion}></option>{/each}</datalist><span class="font-mono text-xs">{skillName.length}/100</span></label>
                    <label class="grid max-w-135 gap-2 font-archivo text-base sm:text-lg">Kategori<select bind:value={skillCategory} required class="modal-input"><option value="">Pilih Kategori...</option>{#each skillCategories as category}<option value={category.id}>{category.name}</option>{/each}</select></label>
                    <label class="grid gap-2 font-archivo text-base sm:text-lg">Materi PDF<input required={!editingSkillId} type="file" accept="application/pdf,.pdf" onchange={selectSkillMaterial} class="modal-input" /></label>
                    <p class="font-mono text-xs">{editingSkillId ? "Pilih file baru bila ingin mengganti materi." : "Wajib PDF asli, maksimal 5 MB."}</p>
                    {#if skillError}<p class="font-mono text-xs text-[#b3261e]">{skillError}</p>{/if}
                    <div class="flex justify-end"><button type="submit" disabled={skillSubmitting} class="button-lift bg-[#48b3cf] px-5 py-2 font-archivo text-base shadow-[4px_4px_0_#000] disabled:opacity-50" style="--button-complement: #ff006e">{skillSubmitting ? "Menyimpan..." : editingSkillId ? "Simpan" : "Tambahkan"}</button></div>
                </form>
            </dialog>
        {:else}
            <dialog open aria-labelledby="achievement-modal-title" class="relative dashboard-enter max-h-[90vh] w-full max-w-3xl overflow-y-auto border-4 border-pitch-black bg-off-white p-0 shadow-[10px_10px_0_#000]">
                <header class="sticky top-0 z-10 flex items-center justify-between border-b-4 border-pitch-black bg-laser-pink px-5 py-3 sm:px-7"><h2 id="achievement-modal-title" class="font-archivo text-xl font-bold sm:text-2xl">{editingAchievementId ? "Edit Sertifikasi" : "Tambahkan Prestasi"}</h2><button type="button" onclick={closeModal} aria-label="Close achievement modal" class="text-4xl leading-none">×</button></header>
                <form onsubmit={(event) => { event.preventDefault(); addAchievement(); }} class="grid gap-5 px-5 py-6 sm:grid-cols-[88px_1fr] sm:px-8 sm:py-8">
                    <div class="flex h-20 w-20 items-center justify-center border-2 border-pitch-black bg-[#ffe477] text-4xl shadow-[4px_4px_0_#000]">🏅</div>
                    <div class="grid gap-6">
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Nama Prestasi<input bind:value={achievementName} maxlength="255" required placeholder="Masukkan Nama Prestasi.." class="modal-input" /></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Deskripsi<textarea bind:value={achievementDescription} maxlength="1000" required placeholder="Ceritakan prestasi ini..." class="modal-input min-h-20"></textarea><span class="font-mono text-xs">{achievementDescription.length}/1000</span></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Organisasi Penerbit<input bind:value={achievementOrganization} maxlength="100" placeholder="Nama Organisasi Penerbit..." class="modal-input" /></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Tanggal Terbit<input bind:value={achievementIssuedDate} required class="modal-input" type="date" /></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Tanggal Kadaluwarsa<input bind:value={achievementExpiryDate} required class="modal-input" type="date" /></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Tingkat<select bind:value={achievementLevel} class="modal-input"><option value="">Pilih tingkat</option><option>Nasional</option><option>Internasional</option></select></label>
                        <label class="grid gap-2 font-archivo text-base sm:text-lg">Sertifikat pendukung<input required={!editingAchievementId} type="file" accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png" onchange={selectCertificate} class="modal-input" /></label>
                        <p class="font-mono text-[10px]">{editingAchievementId ? "Pilih file baru bila ingin mengganti sertifikat." : "Wajib diunggah. PDF, JPG, JPEG, atau PNG asli, maksimal 5 MB."}</p>
                        <div class="flex justify-end">{#if achievementError}<p class="font-archivo text-lg text-[#b3261e]">{achievementError}</p>{/if}</div>
                        <div class="flex justify-end">
            <button type="submit" disabled={achievementSubmitting} class="button-lift bg-[#48b3cf] px-7 py-2 font-archivo text-lg shadow-[4px_4px_0_#000] disabled:opacity-50" style="--button-complement: #ccff00">
                {achievementSubmitting ? "Menyimpan..." : editingAchievementId ? "Simpan" : "Validasi"}
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
