<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    let fullName = "";
    let email = "";
    let phone = "";
    let city = "";
    let availableAt = "";
    /** @type {Array<{id: number|string, name: string, category_skills?: string}>} */
    let skills = [];
    let skillsLoading = true;
    let skillId = "";
    let skillName = "";
    let skillCategory = "";
    let skillDescription = "";
    /** @type {File|null} */
    let proposalFile = null;
    /** @type {HTMLInputElement|undefined} */
    let fileInput;
    let error = "";
    let submitted = false;
    let submitting = false;
    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    const localDateTime = () => {
        const date = new Date();
        date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        return date.toISOString().slice(0, 16);
    };

    onMount(async () => {
        const token = localStorage.getItem("auth_token");
        if (!token) {
            skillsLoading = false;
            push("/login");
            return;
        }

        try {
            const headers = { Accept: "application/json", Authorization: `Bearer ${token}` };
            const [userResponse, skillsResponse] = await Promise.all([
                fetch(`${backendUrl}/api/user`, { headers }),
                fetch(`${backendUrl}/api/skills`, { headers }),
            ]);

            if (userResponse.status === 401 || skillsResponse.status === 401) {
                push("/login");
                return;
            }
            if (!userResponse.ok || !skillsResponse.ok) throw new Error("Gagal mengambil data profil dan skill.");

            const user = await userResponse.json();
            const skillResult = await skillsResponse.json();
            fullName = user.name || "";
            email = user.email || "";
            skills = skillResult.data || [];
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Gagal mengambil data profil dan skill.";
        } finally {
            skillsLoading = false;
        }
    });

    function selectSkill() {
        const selectedSkill = skills.find((skill) => String(skill.id) === String(skillId));
        skillName = selectedSkill?.name || "";
        skillCategory = selectedSkill?.category_skills || "";
    }

    function validatePhone() {
        phone = phone.replace(/\D/g, "").slice(0, 14);
        if (!/^08[0-9]{8,12}$/.test(phone)) {
            return "Nomor telepon harus diawali 08 dan berisi 10 sampai 14 angka.";
        }
        return "";
    }

    /** @param {Event & {currentTarget: HTMLInputElement}} event */
    function handlePhoneInput(event) {
        phone = event.currentTarget.value.replace(/\D/g, "").slice(0, 14);
        error = "";
    }

    /** @param {Event & {currentTarget: HTMLInputElement}} event */
    function selectFile(event) {
        const file = event.currentTarget.files?.[0];
        if (!file) return;

        if (file.type !== "application/pdf" && !file.name.toLowerCase().endsWith(".pdf")) {
            error = "File proposal harus berformat PDF.";
            event.currentTarget.value = "";
            proposalFile = null;
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            error = "Ukuran file maksimal 5 MB.";
            event.currentTarget.value = "";
            proposalFile = null;
            return;
        }

        proposalFile = file;
        error = "";
    }

    /** @param {SubmitEvent} event */
    async function submitProposal(event) {
        event.preventDefault();
        error = "";

        if (!proposalFile) {
            error = "Silakan masukkan file proposal dalam format PDF.";
            return;
        }
        if (!skillId || !skillName.trim()) {
            error = "Pilih skill yang sudah kamu upload di profil.";
            return;
        }
        const phoneError = validatePhone();
        if (phoneError) {
            error = phoneError;
            return;
        }

        submitting = true;

        try {
            const body = new FormData();
            body.append("full_name", fullName.trim());
            body.append("email", email.trim());
            body.append("phone", phone.trim());
            body.append("city", city.trim());
            body.append("available_at", availableAt);
            if (skillId) body.append("skill_id", String(skillId));
            body.append("skill_description", skillDescription.trim());
            body.append("proposal_file", proposalFile);

            const response = await fetch(`${backendUrl}/api/proposals`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
                body,
            });

            const data = await response.json().catch(() => ({}));
            if (response.status === 401) {
                push("/login");
                return;
            }
            if (!response.ok) {
                const validationMessage = data.errors
                    ? Object.values(data.errors).flat().join(" ")
                    : data.message;
                throw new Error(validationMessage || "Proposal gagal dikirim.");
            }

            push("/swapp");
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Proposal gagal dikirim.";
        } finally {
            submitting = false;
        }
    }

    function resetForm() {
        submitted = false;
        fullName = "";
        email = "";
        phone = "";
        city = "";
        availableAt = "";
        skillId = "";
        skillName = "";
        skillCategory = "";
        skillDescription = "";
        proposalFile = null;
        if (fileInput) fileInput.value = "";
    }
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain">
        </a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 max-w-220">
        <a href="/#/swapp" class="mb-6 inline-flex items-center gap-2 font-mono text-xs uppercase transition-transform hover:-translate-x-1">
            <span aria-hidden="true" class="text-lg">&larr;</span>
            Kembali ke barter
        </a>

        <div class="mb-8 border-l-8 border-neon-yellow pl-5">
            <p class="font-mono text-sm uppercase tracking-wide text-laser-pink">Share your skill</p>
            <h1 class="font-anton text-5xl uppercase leading-none sm:text-6xl">Buat proposal</h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed sm:text-lg">Kenalkan dirimu dan skill yang ingin kamu tawarkan kepada teman belajar berikutnya.</p>
        </div>

        {#if submitted}
            <div class="border-3 border-pitch-black bg-cyber-lime p-6 shadow-[8px_8px_0_#000] sm:p-10">
                <p class="font-mono text-xs uppercase">Proposal siap dikirim</p>
                <h2 class="mt-3 font-anton text-4xl uppercase">Terima kasih, {fullName || "teman"}!</h2>
                <p class="mt-3 max-w-xl leading-relaxed">Data proposalmu sudah tervalidasi. Hubungkan halaman ini ke endpoint backend untuk menyimpan proposal ke server.</p>
                <div class="mt-7 flex flex-wrap gap-4">
                    <button type="button" onclick={resetForm} class="button-lift border-2 border-pitch-black bg-white px-6 py-3 font-mono text-xs uppercase shadow-[4px_4px_0_#000]" style="--button-complement: #ff006e">Buat proposal lain</button>
                    <button type="button" onclick={() => push("/swapp")} class="button-lift border-2 border-pitch-black bg-laser-pink px-6 py-3 font-mono text-xs uppercase shadow-[4px_4px_0_#000]" style="--button-complement: #00d9ff">Lihat barter</button>
                </div>
            </div>
        {:else}
            <form onsubmit={submitProposal} novalidate class="border-3 border-pitch-black bg-off-white p-5 shadow-[8px_8px_0_#000] sm:p-8">
                <fieldset>
                    <legend class="font-anton text-3xl uppercase">Data diri</legend>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Nama lengkap
                            <input bind:value={fullName} required type="text" placeholder="Nama kamu" class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]" />
                        </label>
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Email
                            <input bind:value={email} required type="email" placeholder="nama@email.com" class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]" />
                        </label>
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Nomor telepon
                            <input bind:value={phone} required type="tel" inputmode="numeric" maxlength="14" oninput={handlePhoneInput} placeholder="08xxxxxxxxxx" class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]" />
                            {#if phone && !/^08[0-9]{8,12}$/.test(phone.replace(/\D/g, ""))}
                                <span class="font-mono text-[10px] normal-case text-red-600">Nomor harus diawali 08 dan panjangnya 10-14 angka.</span>
                            {/if}
                        </label>
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Kota domisili
                            <input bind:value={city} required type="text" placeholder="Kota kamu" class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]" />
                        </label>
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase sm:col-span-2">
                            Jadwal mulai sesi
                            <input bind:value={availableAt} required type="datetime-local" min={localDateTime()} class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]" />
                            <span class="font-mono text-[10px] normal-case text-pitch-black/60">Request yang belum disetujui provider akan hilang setelah 1 hari.</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="mt-9 border-t-2 border-pitch-black pt-7">
                    <legend class="font-anton text-3xl uppercase">Skills</legend>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Nama skill
                            <input bind:value={skillName} readonly required type="text" placeholder="Pilih skill di samping" class="border-2 border-pitch-black bg-gray-100 px-4 py-3 font-archivo text-base normal-case outline-none" />
                        </label>
                        <label class="flex flex-col gap-2 font-mono text-xs uppercase">
                            Skill yang dipunya
                            <select bind:value={skillId} onchange={selectSkill} required disabled={skillsLoading || skills.length === 0} class="border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00] disabled:cursor-not-allowed disabled:bg-gray-100">
                                <option value="">{skillsLoading ? "Memuat skill..." : skills.length ? "Pilih skill" : "Belum ada skill"}</option>
                                {#each skills as skill}
                                    <option value={skill.id}>{skill.name}</option>
                                {/each}
                            </select>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="mt-9 border-t-2 border-pitch-black pt-7">
                    <legend class="font-anton text-3xl uppercase">Deskripsi skill</legend>
                    <label class="mt-5 flex flex-col gap-2 font-mono text-xs uppercase">
                        Skill yang kamu tawarkan
                        <textarea bind:value={skillDescription} required rows="6" placeholder="Ceritakan skill, pengalaman, dan bentuk barter yang kamu inginkan..." class="resize-y border-2 border-pitch-black bg-white px-4 py-3 font-archivo text-base normal-case outline-none transition-shadow focus:shadow-[4px_4px_0_#ccff00]"></textarea>
                    </label>
                </fieldset>

                <fieldset class="mt-9 border-t-2 border-pitch-black pt-7">
                    <legend class="font-anton text-3xl uppercase">Masukkan file PDF</legend>
                    <label for="proposal-file" class="mt-5 flex cursor-pointer flex-col items-center justify-center border-3 border-dashed border-pitch-black bg-white px-5 py-10 text-center transition-colors hover:bg-[#ffe477]">
                        <span class="flex h-14 w-14 items-center justify-center border-2 border-pitch-black bg-laser-pink font-anton text-2xl">PDF</span>
                        <span class="mt-4 font-mono text-xs uppercase">{proposalFile ? proposalFile.name : "Klik untuk pilih file"}</span>
                        <span class="mt-2 text-sm">PDF saja, maksimal 5 MB</span>
                        <input id="proposal-file" bind:this={fileInput} onchange={selectFile} accept="application/pdf,.pdf" type="file" class="sr-only" />
                    </label>
                </fieldset>

                {#if error}
                    <p role="alert" class="mt-5 border-2 border-pale-red bg-[#ffd6df] px-4 py-3 font-mono text-xs">{error}</p>
                {/if}

                <button type="submit" disabled={submitting} class="button-lift mt-8 w-full border-2 border-pitch-black bg-neon-yellow px-6 py-4 font-mono text-sm uppercase shadow-[5px_5px_0_#000] disabled:cursor-wait disabled:opacity-60" style="--button-complement: #ff006e">{submitting ? "Mengirim..." : "Kirim proposal"} <span aria-hidden="true">&rarr;</span></button>
            </form>
        {/if}
    </section>
</main>
