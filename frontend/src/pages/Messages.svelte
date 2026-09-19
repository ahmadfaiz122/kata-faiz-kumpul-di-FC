<script>
    import { onMount } from "svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let receivedMessages = $state([]);
    let sentMessages = $state([]);
    let loading = $state(true);
    let error = $state("");
    let reviewTransactionId = $state(null);
    let reviewRating = $state(0);
    let reviewReputation = $state("");
    let reviewComment = $state("");

    function messageFor(transaction, direction) {
        const otherUser = direction === "received" ? transaction.requester : transaction.provider;
        return {
            id: transaction.id,
            transaction,
            name: otherUser?.name || "Pengguna Swapp",
            subject: transaction.mode === "credit" ? "Permintaan belajar dengan credit" : "Permintaan barter skill",
            preview: direction === "received"
                ? `${transaction.requester_skill?.name || "Requester"} mengajukan transaksi.`
                : transaction.status === "active" ? "Provider sudah menyetujui transaksi." : "Menunggu persetujuan provider.",
            time: transaction.starts_at ? new Date(transaction.starts_at).toLocaleString("id-ID") : "Terjadwal",
            unread: transaction.status === "pending",
            color: direction === "received" ? "bg-electric-cyan" : "bg-laser-pink",
        };
    }

    async function loadTransactions() {
        const response = await fetch(`${backendUrl}/api/transactions`, {
            headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.message || "Transaksi gagal dimuat.");
        const transactions = result.data || [];
        receivedMessages = transactions.filter((item) => item.is_provider).map((item) => messageFor(item, "received"));
        sentMessages = transactions.filter((item) => !item.is_provider).map((item) => messageFor(item, "sent"));
    }

    async function approveTransaction(message) {
        error = "";
        const response = await fetch(`${backendUrl}/api/transactions/${message.id}/approve`, {
            method: "POST",
            headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            error = result.message || "Transaksi gagal disetujui.";
            return;
        }
        await loadTransactions();
    }

    async function submitReview(message) {
        if (!reviewRating || !reviewReputation) {
            error = "Pilih rating dan reputation terlebih dahulu.";
            return;
        }
        const response = await fetch(`${backendUrl}/api/transactions/${message.id}/review`, {
            method: "POST",
            headers: { Accept: "application/json", "Content-Type": "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
            body: JSON.stringify({ rating: reviewRating, reputation: reviewReputation, comment: reviewComment }),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            error = result.message || "Review gagal dikirim.";
            return;
        }
        reviewTransactionId = null;
        reviewRating = 0;
        reviewReputation = "";
        reviewComment = "";
        await loadTransactions();
    }

    async function downloadMaterial(material) {
        const response = await fetch(`${backendUrl}${material.url}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
        });
        if (!response.ok) {
            error = "Materi belum dapat diakses.";
            return;
        }
        const blobUrl = URL.createObjectURL(await response.blob());
        const link = document.createElement("a");
        link.href = blobUrl;
        link.download = `${material.name}.pdf`;
        link.click();
        URL.revokeObjectURL(blobUrl);
    }

    onMount(async () => {
        try {
            await loadTransactions();
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Transaksi gagal dimuat.";
        } finally {
            loading = false;
        }
    });
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

    <section class="dashboard-enter dashboard-enter-delay-1 mx-auto mt-14 max-w-240">
        <div class="mb-10 border-l-8 border-neon-yellow pl-5">
            <p class="font-mono text-sm uppercase tracking-wide text-laser-pink">Stay connected</p>
            <h1 class="font-anton text-5xl uppercase leading-none sm:text-6xl">Messages</h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed sm:text-lg">Percakapan seputar skill, barter, dan sesi belajar kamu.</p>
        </div>

        <div class="grid gap-10 lg:grid-cols-2">
            <section aria-labelledby="received-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="received-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Received</h2>
                    <span class="bg-laser-pink px-2 py-1 font-mono text-[10px] font-bold uppercase">1 unread</span>
                </div>
                {#if loading}<p class="font-mono text-sm">Memuat transaksi...</p>{/if}
                <div class="grid gap-4">
                    {#each receivedMessages as message}
                        <div class="message-card {message.color} text-left">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-pitch-black bg-off-white font-mono font-bold shadow-[3px_3px_0_#000]">{message.name.slice(0, 1)}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="truncate font-mono text-sm font-bold uppercase">{message.name}</span>
                                        <span class="shrink-0 font-mono text-[10px]">{message.time}</span>
                                    </div>
                                    <p class="mt-2 truncate font-archivo text-sm font-bold">{message.subject}</p>
                                    <p class="mt-1 truncate font-archivo text-xs">{message.preview}</p>
                                </div>
                                {#if message.unread}<span class="mt-1 h-3 w-3 shrink-0 rounded-full border-2 border-pitch-black bg-laser-pink" aria-label="Unread"></span>{/if}
                            </div>
                            {#if message.transaction.status === "pending"}
                                <button type="button" onclick={() => approveTransaction(message)} class="mt-4 border-2 border-pitch-black bg-cyber-lime px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Approve transaksi</button>
                            {:else if message.transaction.status === "active"}
                                <a href={message.transaction.whatsapp_url || "#"} target="_blank" rel="noreferrer" class="mt-4 inline-block border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Buka WhatsApp</a>
                                {#each message.transaction.materials || [] as material}
                                    <button type="button" onclick={() => downloadMaterial(material)} class="ml-2 mt-4 inline-block border-2 border-pitch-black bg-electric-cyan px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Materi: {material.name}</button>
                                {/each}
                            {/if}
                        </div>
                    {/each}
                </div>
            </section>

            <section aria-labelledby="sent-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="sent-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Sent</h2>
                    <span class="font-mono text-[10px] font-bold uppercase">{sentMessages.length} messages</span>
                </div>
                <div class="grid gap-4">
                    {#each sentMessages as message}
                        <div class="message-card {message.color} text-left">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-pitch-black bg-off-white font-mono font-bold shadow-[3px_3px_0_#000]">{message.name.slice(0, 1)}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="truncate font-mono text-sm font-bold uppercase">{message.name}</span>
                                        <span class="shrink-0 font-mono text-[10px]">{message.time}</span>
                                    </div>
                                    <p class="mt-2 truncate font-archivo text-sm font-bold">{message.subject}</p>
                                    <p class="mt-1 truncate font-archivo text-xs">{message.preview}</p>
                                </div>
                            </div>
                            {#if message.transaction.status === "active"}
                                <a href={message.transaction.whatsapp_url || "#"} target="_blank" rel="noreferrer" class="mt-4 inline-block border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Akses transaksi</a>
                                {#each message.transaction.materials || [] as material}
                                    <button type="button" onclick={() => downloadMaterial(material)} class="ml-2 mt-4 inline-block border-2 border-pitch-black bg-electric-cyan px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Materi: {material.name}</button>
                                {/each}
                            {:else if message.transaction.status === "completed" && !message.transaction.review}
                                <button type="button" onclick={() => reviewTransactionId = message.id} class="mt-4 border-2 border-pitch-black bg-neon-yellow px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Beri review</button>
                                {#if reviewTransactionId === message.id}
                                    <div class="mt-3 grid gap-2 border-t-2 border-pitch-black pt-3">
                                        <label class="font-mono text-xs">Rating (1-5)<input bind:value={reviewRating} type="number" min="1" max="5" class="border-2 border-pitch-black px-2 py-1" /></label>
                                        <label class="font-mono text-xs">Reputation<select bind:value={reviewReputation} class="border-2 border-pitch-black px-2 py-1"><option value="">Pilih</option><option value="sad">Sedih</option><option value="flat">Flat</option><option value="smile">Senyum</option></select></label>
                                        <textarea bind:value={reviewComment} placeholder="Komentar (opsional)" class="border-2 border-pitch-black px-2 py-1 font-mono text-xs"></textarea>
                                        <button type="button" onclick={() => submitReview(message)} class="border-2 border-pitch-black bg-cyber-lime px-3 py-2 font-mono text-xs font-bold">Kirim review</button>
                                    </div>
                                {/if}
                            {/if}
                        </div>
                    {/each}
                </div>
            </section>
        </div>
    </section>
</main>

<style>
    .message-card {
        width: 100%;
        border: 2px solid #000;
        padding: 1rem;
        box-shadow: 5px 5px 0 #000;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .message-card:hover {
        transform: translateY(-3px);
        box-shadow: 7px 7px 0 #000;
    }

    .message-card:active {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0 #000;
    }
</style>
