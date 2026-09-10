<script>
    import { onMount } from "svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let saved = false;
    let loading = true;
    let saving = false;
    let error = "";
    let avatarInput;
    let avatarFile = null;
    let profile = { name: "", username: "", alias: "", bio: "", email: "", nim: "", photo: "", linkedin: "", instagram: "", github: "" };

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
                instagram: stored.instagram || "",
            };
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profile.";
        } finally {
            loading = false;
        }
    });

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
                window.location.href = "/#/login";
                return;
            }
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || "Profile gagal disimpan.");
            }
            window.location.href = "/#/profile";
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Profile gagal disimpan.";
        } finally {
            saving = false;
        }
    }

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
        error = "";
        avatarFile = file;
        profile.photo = URL.createObjectURL(file);
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
