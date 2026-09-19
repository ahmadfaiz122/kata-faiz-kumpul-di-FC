<script>
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import logo from "../assets/logo.webp";

    const receivedMessages = [
        {
            id: 1,
            name: "Alya Putri",
            subject: "Belajar Brand Strategy",
            preview: "Hai, aku tertarik bertukar skill dengan kamu.",
            time: "10:42",
            unread: true,
            color: "bg-electric-cyan"
        },
        {
            id: 2,
            name: "Ibna Clevaro",
            subject: "Proposal barter",
            preview: "Boleh kita jadwalkan sesi minggu ini?",
            time: "Kemarin",
            unread: false,
            color: "bg-neon-yellow"
        }
    ];

    const sentMessages = [
        {
            id: 3,
            name: "Reinzal Kinas",
            subject: "Re: Svelte untuk pemula",
            preview: "Aku sudah mengirim detail materi belajarnya.",
            time: "Senin",
            unread: false,
            color: "bg-laser-pink"
        },
        {
            id: 4,
            name: "Kastama",
            subject: "Kolaborasi design",
            preview: "Terima kasih, aku tunggu kabar selanjutnya.",
            time: "12 Sep",
            unread: false,
            color: "bg-[#ffa174]"
        }
    ];
</script>

<main class="min-h-screen overflow-hidden px-5 py-6 sm:px-10 lg:px-14">
    <header class="dashboard-enter relative z-30 mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
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
                <div class="grid gap-4">
                    {#each receivedMessages as message}
                        <button type="button" class="message-card {message.color} text-left">
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
                        </button>
                    {/each}
                </div>
            </section>

            <section aria-labelledby="sent-title">
                <div class="mb-4 flex items-center justify-between border-b-3 border-pitch-black pb-3">
                    <h2 id="sent-title" class="font-mono text-xl font-bold uppercase sm:text-2xl">Sent</h2>
                    <span class="font-mono text-[10px] font-bold uppercase">2 messages</span>
                </div>
                <div class="grid gap-4">
                    {#each sentMessages as message}
                        <button type="button" class="message-card {message.color} text-left">
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
                        </button>
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
