<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    /** @type {{ params?: { id?: string } }} */
    let { params = {} } = $props();

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let messages = $state([]);
    let meta = $state(null);
    let conversation = $state(null);
    let draft = $state("");
    let loading = $state(true);
    let sending = $state(false);
    let error = $state("");
    let pollTimer;

    const headers = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
    });

    async function loadConversation(initial = false) {
        try {
            const lastId = messages.at(-1)?.id || 0;
            const response = await fetch(`${backendUrl}/api/conversations/${params?.id}?after_id=${lastId}`, { headers: headers() });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Chat gagal dimuat.");

            const incoming = result.data || [];
            const knownIds = new Set(messages.map((message) => message.id));
            messages = [...messages, ...incoming.filter((message) => !knownIds.has(message.id))];
            meta = result.meta || null;
            conversation = result.conversation || conversation;
            if (meta?.is_archived) clearInterval(pollTimer);
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Chat gagal dimuat.";
        } finally {
            if (initial) loading = false;
        }
    }

    async function sendMessage() {
        if (!draft.trim() || !meta?.can_send || sending) return;
        sending = true;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/conversations/${params?.id}/messages`, {
                method: "POST",
                headers: { ...headers(), "Content-Type": "application/json" },
                body: JSON.stringify({ body: draft.trim() }),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Pesan gagal dikirim.");
            messages = [...messages, result.data];
            draft = "";
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Pesan gagal dikirim.";
        } finally {
            sending = false;
        }
    }

    onMount(async () => {
        if (!localStorage.getItem("auth_token")) {
            push("/login");
            return;
        }
        await loadConversation(true);
        if (meta?.can_send) pollTimer = setInterval(() => loadConversation(), 2500);
        return () => clearInterval(pollTimer);
    });
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/timeline" aria-label="Faiz home" class="h-11 w-28 transition-transform hover:-translate-y-1 sm:h-14 sm:w-36">
            <img src={logo} alt="Faiz logo" class="h-full w-full scale-[1.2] object-contain" />
        </a>
        <Navbar />
        <div class="justify-self-end"><ProfileDropdown /></div>
    </header>

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 max-w-190">
        <button type="button" onclick={() => push("/messages")} class="mb-6 font-mono text-xs uppercase transition-transform hover:-translate-x-1">&larr; Kembali ke transaksi</button>
        {#if error}<p role="alert" class="mb-6 border-2 border-pitch-black bg-[#ffd6df] px-4 py-3 font-mono text-xs">{error}</p>{/if}
        <header class="border-4 border-pitch-black bg-electric-cyan p-5 shadow-[7px_7px_0_#000] sm:p-7">
            <p class="font-mono text-[10px] font-bold uppercase">{meta?.is_archived ? "Arsip transaksi" : "Ruang transaksi"}</p>
            <h1 class="mt-2 font-anton text-5xl uppercase leading-none">Chat sesi</h1>
            <div class="mt-5 flex flex-wrap gap-2 font-mono text-[10px] font-bold uppercase">
                <span class="border-2 border-pitch-black bg-off-white px-2 py-1">Status: {meta?.status || "Memuat"}</span>
                {#if meta?.read_only}<span class="border-2 border-pitch-black bg-neon-yellow px-2 py-1">Read-only</span>{/if}
                {#if meta?.starts_at}<span class="border-2 border-pitch-black bg-off-white px-2 py-1">Mulai: {new Date(meta.starts_at).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" })}</span>{/if}
                {#if meta?.ends_at}<span class="border-2 border-pitch-black bg-off-white px-2 py-1">Selesai: {new Date(meta.ends_at).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" })}</span>{/if}
            </div>
        </header>

        <section class="mt-8 border-4 border-pitch-black bg-off-white p-4 shadow-[7px_7px_0_#000] sm:p-6" aria-label="Percakapan transaksi">
            <div class="chat-window">
                {#if loading}<p class="font-mono text-xs">Memuat chat...</p>{/if}
                {#if !loading && messages.length === 0}<p class="font-mono text-xs">Belum ada pesan.</p>{/if}
                {#each messages as message}
                    <article class="chat-bubble {message.is_mine ? 'chat-bubble-mine' : ''}">
                        <p class="font-mono text-[10px] font-bold uppercase">{message.sender?.name || "Pengguna"}</p>
                        <p class="mt-1 whitespace-pre-wrap font-archivo text-sm">{message.body}</p>
                        <time class="mt-2 block font-mono text-[9px] opacity-60">{new Date(message.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}</time>
                    </article>
                {/each}
            </div>
            {#if meta?.can_send}
                <form class="mt-5 flex gap-2" onsubmit={(event) => { event.preventDefault(); sendMessage(); }}>
                    <textarea bind:value={draft} rows="3" maxlength="2000" placeholder="Tulis pesan untuk sesi ini..." class="min-w-0 flex-1 border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs"></textarea>
                    <button type="submit" disabled={sending || !draft.trim()} class="self-end border-2 border-pitch-black bg-cyber-lime px-4 py-3 font-mono text-xs font-bold shadow-[3px_3px_0_#000] disabled:opacity-50">{sending ? "..." : "Kirim"}</button>
                </form>
            {:else if !loading}
                <p class="mt-5 border-2 border-pitch-black bg-neon-yellow px-3 py-2 font-mono text-xs font-bold">{meta?.read_only ? "Sesi selesai. Arsip chat dapat dibaca, tetapi pesan baru dinonaktifkan." : "Chat belum aktif."}</p>
            {/if}
        </section>
    </section>
</main>

<style>
    .chat-window {
        display: grid;
        gap: 0.75rem;
        min-height: 20rem;
        max-height: 32rem;
        overflow-y: auto;
        border: 2px solid #000;
        background: #d8d8d8;
        padding: 1rem;
    }

    .chat-bubble {
        width: fit-content;
        max-width: min(85%, 42rem);
        border: 2px solid #000;
        background: #fff;
        padding: 0.75rem;
        box-shadow: 3px 3px 0 #000;
    }

    .chat-bubble-mine {
        justify-self: end;
        background: #b6ff00;
    }
</style>
