<script>
    import { onMount } from "svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";
    import { push } from "svelte-spa-router";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let receivedMessages = $state([]);
    let sentMessages = $state([]);
    let loading = $state(true);
    let error = $state("");
    let reviewTransactionId = $state(null);
    let reviewRating = $state(0);
    let reviewReputation = $state("");
    let reviewComment = $state("");
    let creditLedger = $state([]);
    let showMoreReceived = $state(false);
    let showMoreSent = $state(false);
    let selectedConversation = $state(null);
    let chatMessages = $state([]);
    let chatMeta = $state(null);
    let chatDraft = $state("");
    let chatLoading = $state(false);
    let chatSending = $state(false);
    let pollTimer;

    const reputationOptions = [
        { value: "very_bad", emoji: "😞", label: "Kurang baik", delta: -2 },
        { value: "needs_improvement", emoji: "🙁", label: "Perlu ditingkatkan", delta: -1 },
        { value: "neutral", emoji: "😐", label: "Biasa", delta: 0 },
        { value: "good", emoji: "🙂", label: "Baik", delta: 1 },
        { value: "excellent", emoji: "😄", label: "Sangat baik", delta: 2 },
    ];

    function statusInfo(status) {
        return {
            pending: { label: "Menunggu", className: "status-pending" },
            scheduled: { label: "Terjadwal", className: "status-scheduled" },
            active: { label: "Aktif", className: "status-active" },
            completed: { label: "Selesai", className: "status-completed" },
            rejected: { label: "Ditolak", className: "status-ended" },
            cancelled: { label: "Dibatalkan", className: "status-ended" },
            expired: { label: "Kedaluwarsa", className: "status-ended" },
        }[status] || { label: status, className: "status-ended" };
    }

    function messageFor(transaction, direction) {
        const otherUser = direction === "received" ? transaction.requester : transaction.provider;
        return {
            id: transaction.id,
            transaction,
            name: otherUser?.name || transaction.proposal?.name || "Pengguna Swapp",
            subject: transaction.mode === "credit" ? "Permintaan belajar dengan credit" : "Permintaan barter skill",
            preview: direction === "received"
                ? transaction.status === "pending" ? `${transaction.requester_skill?.name || "Requester"} mengajukan transaksi.` : `Status transaksi: ${transaction.status}.`
                : transaction.status === "active" ? "Provider sudah menyetujui transaksi." : `Status transaksi: ${transaction.status}.`,
            time: transaction.starts_at ? new Date(transaction.starts_at).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" }) : "Terjadwal",
            unread: transaction.status === "pending",
            color: direction === "received" ? "bg-electric-cyan" : "bg-laser-pink",
        };
    }

    async function loadTransactions() {
        const headers = { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` };
        const transactionsResponse = await fetch(`${backendUrl}/api/transactions`, { headers });
        const result = await transactionsResponse.json().catch(() => ({}));
        if (!transactionsResponse.ok) throw new Error(result.message || "Transaksi gagal dimuat.");
        const transactions = result.data || [];
        receivedMessages = transactions.filter((item) => item.is_provider).map((item) => messageFor(item, "received"));
        sentMessages = transactions.filter((item) => !item.is_provider).map((item) => messageFor(item, "sent"));

        fetch(`${backendUrl}/api/credits/ledger`, { headers })
            .then((response) => response.json().then((data) => ({ response, data })))
            .then(({ response, data }) => {
                if (!response.ok) throw new Error(data.message || "Riwayat credit gagal dimuat.");
                creditLedger = data.data?.data || [];
            })
            .catch((requestError) => {
                error = requestError instanceof Error ? requestError.message : "Riwayat credit gagal dimuat.";
            });
    }

    async function openConversation(transactionId) {
        const transaction = [...receivedMessages, ...sentMessages].find((item) => Number(item.id) === Number(transactionId))?.transaction;
        if (!transaction?.conversation_id) {
            error = "Chat baru tersedia setelah transaksi disetujui.";
            return;
        }
        push(`/chat/${transaction.conversation_id}`);
    }

    async function loadChat(initial = false) {
        if (!selectedConversation) return;
        if (initial) chatLoading = true;
        try {
            const lastId = chatMessages.at(-1)?.id || 0;
            const response = await fetch(`${backendUrl}/api/conversations/${selectedConversation.id}?after_id=${lastId}`, {
                headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Chat gagal dimuat.");
            const incoming = result.data || [];
            const knownIds = new Set(chatMessages.map((message) => message.id));
            chatMessages = [...chatMessages, ...incoming.filter((message) => !knownIds.has(message.id))];
            chatMeta = result.meta || null;
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Chat gagal dimuat.";
        } finally {
            if (initial) chatLoading = false;
        }
    }

    function startPolling() {
        clearInterval(pollTimer);
        pollTimer = setInterval(() => loadChat(), 2500);
    }

    function closeChat() {
        clearInterval(pollTimer);
        selectedConversation = null;
        chatMessages = [];
        chatMeta = null;
        chatDraft = "";
    }

    async function sendChatMessage() {
        if (!selectedConversation || !chatDraft.trim() || !chatMeta?.can_send) return;
        chatSending = true;
        error = "";
        try {
            const response = await fetch(`${backendUrl}/api/conversations/${selectedConversation.id}/messages`, {
                method: "POST",
                headers: { Accept: "application/json", "Content-Type": "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
                body: JSON.stringify({ body: chatDraft.trim() }),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || "Pesan gagal dikirim.");
            chatMessages = [...chatMessages, result.data];
            chatDraft = "";
        } catch (requestError) {
            error = requestError instanceof Error ? requestError.message : "Pesan gagal dikirim.";
        } finally {
            chatSending = false;
        }
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

    async function updateTransaction(message, action) {
        error = "";
        const response = await fetch(`${backendUrl}/api/transactions/${message.id}/${action}`, {
            method: "POST",
            headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            error = result.message || "Status transaksi gagal diperbarui.";
            return;
        }
        await loadTransactions();
    }

    async function hideTransaction(message) {
        error = "";
        const response = await fetch(`${backendUrl}/api/transactions/${message.id}/hide`, {
            method: "POST",
            headers: { Accept: "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            error = result.message || "Transaksi gagal disembunyikan.";
            return;
        }
        if (selectedConversation?.transaction_id === message.id) closeChat();
        await loadTransactions();
    }

    async function submitReview(message) {
        if (!reviewRating || !reviewReputation) {
            error = "Pilih rating dan reputasi terlebih dahulu.";
            return;
        }
        const response = await fetch(`${backendUrl}/api/transactions/${message.id}/review`, {
            method: "POST",
            headers: { Accept: "application/json", "Content-Type": "application/json", Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
            body: JSON.stringify({ rating: Number(reviewRating), reputation_category: reviewReputation, comment: reviewComment }),
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
        return () => clearInterval(pollTimer);
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
        {#if error}
            <p role="alert" class="mb-6 border-2 border-pitch-black bg-[#ffd6df] px-4 py-3 font-mono text-xs">{error}</p>
        {/if}

        <div class="grid gap-10 lg:grid-cols-2">
            <section aria-labelledby="received-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="received-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Received</h2>
                    <span class="bg-laser-pink px-2 py-1 font-mono text-[10px] font-bold uppercase">{receivedMessages.filter((message) => message.unread).length} unread</span>
                </div>
                {#if loading}<p class="font-mono text-sm">Memuat transaksi...</p>{/if}
                <div class="grid gap-4">
                    {#if !loading && receivedMessages.length === 0}<p class="border-2 border-pitch-black bg-off-white p-4 font-mono text-xs">Belum ada request masuk.</p>{/if}
                    {#each (showMoreReceived ? receivedMessages : receivedMessages.slice(0, 2)) as message}
                        {@const status = statusInfo(message.transaction.status)}
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
                                    <span class={`status-pill ${status.className}`}>{status.label}</span>
                                    <div class="mt-3 flex flex-wrap gap-2 font-mono text-[10px] uppercase">
                                        <span class="border border-pitch-black bg-off-white px-2 py-1">Skill: {message.transaction.proposal?.skill_name || message.transaction.barterRequest?.skill?.name || "-"}</span>
                                        {#if message.transaction.proposal?.city}<span class="border border-pitch-black bg-off-white px-2 py-1">{message.transaction.proposal.city}</span>{/if}
                                        <span class="border border-pitch-black bg-off-white px-2 py-1">{message.transaction.mode === "credit" ? "1 credit" : "Barter skill"}</span>
                                    </div>
                                    {#if message.transaction.proposal?.skill_description}<p class="mt-2 line-clamp-2 font-archivo text-xs">{message.transaction.proposal.skill_description}</p>{/if}
                                </div>
                                {#if message.unread}<span class="mt-1 h-3 w-3 shrink-0 rounded-full border-2 border-pitch-black bg-laser-pink" aria-label="Unread"></span>{/if}
                            </div>
                            {#if message.transaction.status === "pending"}
                                <button type="button" onclick={() => approveTransaction(message)} class="action-button action-primary mt-4">Approve transaksi</button>
                                <button type="button" onclick={() => updateTransaction(message, "reject")} class="action-button action-danger ml-2 mt-4">Tolak</button>
                            {:else if message.transaction.status === "active" || message.transaction.status === "completed"}
                                <button type="button" onclick={() => openConversation(message.id)} class="action-button action-secondary mt-4">{message.transaction.status === "completed" ? "Buka arsip chat" : "Buka chat transaksi"}</button>
                                {#each message.transaction.materials || [] as material}
                                    <button type="button" onclick={() => downloadMaterial(material)} class="ml-2 mt-4 inline-block border-2 border-pitch-black bg-electric-cyan px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Materi: {material.name}</button>
                                {/each}
                                {#if message.transaction.status === "completed" && !message.transaction.reviewed_by_me}
                                    <button type="button" onclick={() => reviewTransactionId = message.id} class="mt-4 border-2 border-pitch-black bg-neon-yellow px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Beri rating & reputasi</button>
                                    {#if reviewTransactionId === message.id}
                                        <div class="mt-3 grid gap-2 border-t-2 border-pitch-black pt-3">
                                            <fieldset class="review-fieldset"><legend>Rating (1-5)</legend><div class="rating-stars">{#each [1, 2, 3, 4, 5] as rating}<button type="button" aria-label={`Beri rating ${rating}`} class:rating-selected={Number(reviewRating) >= rating} onclick={() => reviewRating = rating}>★</button>{/each}</div></fieldset>
                                            <fieldset class="review-fieldset"><legend>Reputasi</legend><div class="reputation-options">{#each reputationOptions as option}<button type="button" class:reputation-selected={reviewReputation === option.value} onclick={() => reviewReputation = option.value}><span class="reputation-emoji">{option.emoji}</span><span>{option.label}</span><small>{option.delta > 0 ? `+${option.delta}` : option.delta}</small></button>{/each}</div></fieldset>
                                            <textarea bind:value={reviewComment} placeholder="Komentar (opsional)" class="border-2 border-pitch-black px-2 py-1 font-mono text-xs"></textarea>
                                            <button type="button" onclick={() => submitReview(message)} class="border-2 border-pitch-black bg-cyber-lime px-3 py-2 font-mono text-xs font-bold">Kirim review</button>
                                        </div>
                                    {/if}
                                {/if}
                            {:else if message.transaction.status === "rejected" || message.transaction.status === "cancelled" || message.transaction.status === "expired"}
                                <span class="mt-4 inline-block border-2 border-pitch-black bg-off-white px-3 py-2 font-mono text-xs font-bold uppercase">{message.transaction.status}</span>
                            {/if}
                            <button type="button" onclick={() => hideTransaction(message)} class="action-button action-muted mt-4 ml-2">Hapus</button>
                        </div>
                    {/each}
                </div>
                {#if receivedMessages.length > 2}
                    <button type="button" onclick={() => showMoreReceived = !showMoreReceived} class="mt-5 border-2 border-pitch-black bg-neon-yellow px-4 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">{showMoreReceived ? "Tampilkan lebih sedikit" : `See more (${receivedMessages.length - 2})`}</button>
                {/if}
            </section>

            <section aria-labelledby="sent-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="sent-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Sent</h2>
                    <span class="font-mono text-[10px] font-bold uppercase">{sentMessages.length} messages</span>
                </div>
                <div class="grid gap-4">
                    {#if !loading && sentMessages.length === 0}<p class="border-2 border-pitch-black bg-off-white p-4 font-mono text-xs">Belum ada transaksi yang kamu ajukan.</p>{/if}
                    {#each (showMoreSent ? sentMessages : sentMessages.slice(0, 2)) as message}
                        {@const status = statusInfo(message.transaction.status)}
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
                                    <span class={`status-pill ${status.className}`}>{status.label}</span>
                                    <div class="mt-3 flex flex-wrap gap-2 font-mono text-[10px] uppercase">
                                        <span class="border border-pitch-black bg-off-white px-2 py-1">Skill: {message.transaction.proposal?.skill_name || "-"}</span>
                                        {#if message.transaction.proposal?.city}<span class="border border-pitch-black bg-off-white px-2 py-1">{message.transaction.proposal.city}</span>{/if}
                                        <span class="border border-pitch-black bg-off-white px-2 py-1">{message.transaction.mode === "credit" ? "1 credit" : "Barter skill"}</span>
                                    </div>
                                    {#if message.transaction.proposal?.skill_description}<p class="mt-2 line-clamp-2 font-archivo text-xs">{message.transaction.proposal.skill_description}</p>{/if}
                                </div>
                            </div>
                            {#if message.transaction.status === "active" || message.transaction.status === "completed"}
                                <button type="button" onclick={() => openConversation(message.id)} class="action-button action-secondary mt-4">{message.transaction.status === "completed" ? "Buka arsip chat" : "Buka chat transaksi"}</button>
                                {#each message.transaction.materials || [] as material}
                                    <button type="button" onclick={() => downloadMaterial(material)} class="ml-2 mt-4 inline-block border-2 border-pitch-black bg-electric-cyan px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Materi: {material.name}</button>
                                {/each}
                                {#if message.transaction.status === "completed" && !message.transaction.reviewed_by_me}
                                    <button type="button" onclick={() => reviewTransactionId = message.id} class="mt-4 border-2 border-pitch-black bg-neon-yellow px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Beri rating & reputasi</button>
                                    {#if reviewTransactionId === message.id}
                                        <div class="mt-3 grid gap-2 border-t-2 border-pitch-black pt-3">
                                            <fieldset class="review-fieldset"><legend>Rating (1-5)</legend><div class="rating-stars">{#each [1, 2, 3, 4, 5] as rating}<button type="button" aria-label={`Beri rating ${rating}`} class:rating-selected={Number(reviewRating) >= rating} onclick={() => reviewRating = rating}>★</button>{/each}</div></fieldset>
                                            <fieldset class="review-fieldset"><legend>Reputasi</legend><div class="reputation-options">{#each reputationOptions as option}<button type="button" class:reputation-selected={reviewReputation === option.value} onclick={() => reviewReputation = option.value}><span class="reputation-emoji">{option.emoji}</span><span>{option.label}</span><small>{option.delta > 0 ? `+${option.delta}` : option.delta}</small></button>{/each}</div></fieldset>
                                            <textarea bind:value={reviewComment} placeholder="Komentar (opsional)" class="border-2 border-pitch-black px-2 py-1 font-mono text-xs"></textarea>
                                            <button type="button" onclick={() => submitReview(message)} class="border-2 border-pitch-black bg-cyber-lime px-3 py-2 font-mono text-xs font-bold">Kirim review</button>
                                        </div>
                                    {/if}
                                {/if}
                            {:else if message.transaction.status === "pending"}
                                <button type="button" onclick={() => updateTransaction(message, "cancel")} class="mt-4 border-2 border-pitch-black bg-off-white px-3 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Batalkan request</button>
                            {:else if message.transaction.status === "rejected" || message.transaction.status === "cancelled" || message.transaction.status === "expired"}
                                <span class="mt-4 inline-block border-2 border-pitch-black bg-off-white px-3 py-2 font-mono text-xs font-bold uppercase">{message.transaction.status}</span>
                            {/if}
                            <button type="button" onclick={() => hideTransaction(message)} class="action-button action-muted mt-4 ml-2">Hapus</button>
                        </div>
                    {/each}
                </div>
                {#if sentMessages.length > 2}
                    <button type="button" onclick={() => showMoreSent = !showMoreSent} class="mt-5 border-2 border-pitch-black bg-neon-yellow px-4 py-2 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">{showMoreSent ? "Tampilkan lebih sedikit" : `See more (${sentMessages.length - 2})`}</button>
                {/if}
            </section>
        </div>

        <section class="mt-10" aria-labelledby="credit-history-title">
            <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                <h2 id="credit-history-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Riwayat Credit</h2>
                <span class="font-mono text-[10px] font-bold uppercase">{creditLedger.length} aktivitas</span>
            </div>
            {#if creditLedger.length === 0}
                <p class="border-2 border-pitch-black bg-off-white p-4 font-mono text-xs">Belum ada aktivitas credit.</p>
            {:else}
                <div class="grid gap-3 sm:grid-cols-2">
                    {#each creditLedger as entry}
                        <article class="border-2 border-pitch-black bg-off-white p-4 shadow-[4px_4px_0_#000]">
                            <div class="flex items-start justify-between gap-3">
                                <span class="font-mono text-xs font-bold uppercase">{entry.type.replaceAll("_", " ")}</span>
                                <span class="font-mono text-sm font-bold {entry.amount > 0 ? 'text-green-700' : 'text-laser-pink'}">{entry.amount > 0 ? '+' : ''}{entry.amount}</span>
                            </div>
                            <p class="mt-2 font-archivo text-sm">{entry.description}</p>
                            <p class="mt-2 font-mono text-[10px]">Saldo setelah: {entry.balance_after}</p>
                        </article>
                    {/each}
                </div>
            {/if}
        </section>

        {#if selectedConversation}
            <section class="mt-10 border-4 border-pitch-black bg-off-white shadow-[7px_7px_0_#000]" aria-labelledby="transaction-chat-title">
                <header class="flex items-start justify-between gap-4 border-b-4 border-pitch-black bg-electric-cyan p-4 sm:p-5">
                    <div>
                        <p class="font-mono text-[10px] font-bold uppercase">Chat transaction-scoped</p>
                        <h2 id="transaction-chat-title" class="font-anton text-3xl uppercase">{selectedConversation.skill || "Sesi skill"}</h2>
                        <p class="font-mono text-xs">Dengan {selectedConversation.other_user?.name || "Pengguna Swapp"}</p>
                    </div>
                    <button type="button" onclick={closeChat} aria-label="Tutup chat" class="border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-xs font-bold shadow-[3px_3px_0_#000]">Tutup</button>
                </header>
                <div class="grid gap-5 p-4 sm:p-6">
                    <div class="flex flex-wrap gap-2 font-mono text-[10px] font-bold uppercase">
                        <span class="border-2 border-pitch-black bg-neon-yellow px-2 py-1">Status: {chatMeta?.status || selectedConversation.status}</span>
                        {#if chatMeta?.starts_at}<span class="border-2 border-pitch-black bg-white px-2 py-1">Mulai: {new Date(chatMeta.starts_at).toLocaleString("id-ID")}</span>{/if}
                        {#if chatMeta?.ends_at}<span class="border-2 border-pitch-black bg-white px-2 py-1">Selesai: {new Date(chatMeta.ends_at).toLocaleString("id-ID")}</span>{/if}
                    </div>
                    <div class="chat-window">
                        {#if chatLoading}<p class="font-mono text-xs">Memuat chat...</p>{/if}
                        {#if !chatLoading && chatMessages.length === 0}<p class="font-mono text-xs">Belum ada pesan. Chat akan terbuka saat sesi dimulai.</p>{/if}
                        {#each chatMessages as chatMessage}
                            <article class="chat-bubble {chatMessage.is_mine ? 'chat-bubble-mine' : ''}">
                                <p class="font-mono text-[10px] font-bold uppercase">{chatMessage.sender?.name || "Pengguna"}</p>
                                <p class="mt-1 whitespace-pre-wrap font-archivo text-sm">{chatMessage.body}</p>
                                <time class="mt-2 block font-mono text-[9px] opacity-60">{new Date(chatMessage.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}</time>
                            </article>
                        {/each}
                    </div>
                    {#if chatMeta?.can_send}
                        <form class="flex gap-2" onsubmit={(event) => { event.preventDefault(); sendChatMessage(); }}>
                            <textarea bind:value={chatDraft} rows="2" maxlength="2000" placeholder="Tulis pesan untuk sesi ini..." class="min-w-0 flex-1 border-2 border-pitch-black bg-white px-3 py-2 font-mono text-xs"></textarea>
                            <button type="submit" disabled={chatSending || !chatDraft.trim()} class="self-end border-2 border-pitch-black bg-cyber-lime px-4 py-3 font-mono text-xs font-bold shadow-[3px_3px_0_#000] disabled:opacity-50">{chatSending ? "..." : "Kirim"}</button>
                        </form>
                    {:else}
                        <p class="border-2 border-pitch-black bg-neon-yellow px-3 py-2 font-mono text-xs font-bold">Chat terkunci sampai sesi aktif atau karena waktu sesi sudah selesai.</p>
                    {/if}
                </div>
            </section>
        {/if}
    </section>
</main>

<style>
    .status-pill {
        display: inline-flex;
        width: fit-content;
        margin-top: 0.55rem;
        border: 2px solid #000;
        padding: 0.25rem 0.5rem;
        font-family: "Space Mono", monospace;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        box-shadow: 2px 2px 0 #000;
    }

    .status-pending { background: #ffe477; color: #000; }
    .status-scheduled { background: #8bd5ff; color: #000; }
    .status-active { background: #b6ff00; color: #000; }
    .status-completed { background: #2fc7b8; color: #000; }
    .status-ended { background: #f2f2f2; color: #000; }

    .action-button {
        border: 2px solid #000;
        padding: 0.55rem 0.8rem;
        font-family: "Space Mono", monospace;
        font-size: 0.68rem;
        font-weight: 700;
        box-shadow: 3px 3px 0 #000;
        transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
    }

    .action-button:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #000; filter: brightness(1.04); }
    .action-button:active { transform: translate(2px, 2px); box-shadow: 1px 1px 0 #000; }
    .action-button:disabled { cursor: not-allowed; opacity: 0.5; transform: none; box-shadow: 2px 2px 0 #000; }
    .action-primary { background: #b6ff00; }
    .action-secondary { background: #fff; }
    .action-danger { background: #ffb2c1; }
    .action-muted { background: #f2f2f2; }

    .review-fieldset { display: grid; gap: 0.5rem; border: 0; padding: 0; font-family: "Space Mono", monospace; font-size: 0.68rem; font-weight: 700; }
    .rating-stars { display: flex; gap: 0.2rem; }
    .rating-stars button { color: #b9b9b9; font-size: 2rem; line-height: 1; transition: transform 150ms ease, color 150ms ease; }
    .rating-stars button:hover, .rating-stars button.rating-selected { color: #f5a400; transform: translateY(-2px); }
    .reputation-options { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.35rem; }
    .reputation-options button { display: grid; min-height: 4.6rem; place-items: center; gap: 0.15rem; border: 2px solid #000; background: #fff; padding: 0.35rem; font-family: "Space Mono", monospace; font-size: 0.55rem; text-align: center; transition: transform 150ms ease, background 150ms ease; }
    .reputation-options button:hover, .reputation-options button.reputation-selected { background: #8bd5ff; transform: translateY(-2px); }
    .reputation-emoji { font-size: 1.5rem; }
    .reputation-options small { font-size: 0.55rem; }

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

    .chat-window {
        display: grid;
        gap: 0.75rem;
        max-height: 26rem;
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
