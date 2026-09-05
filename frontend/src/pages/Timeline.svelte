<script>
    import { editPage } from "../lib/sharedvar.svelte.js";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";
    import TimelinePost from "../lib/TimelinePost.svelte";

    editPage("Barter Skill");

    const posts = [
        {
            type: "text",
            content: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur."
        },
        { type: "photo", content: "Photo" },
        { type: "text", content: "Bagikan hal baru yang sedang kamu pelajari dan temukan teman untuk bertukar skill bersama." }
    ];

    const leaderboard = ["Kastama", "Ibna", "Reinzal"];
</script>

<main class="min-h-screen bg-[#d8d8d8] px-5 py-7 sm:px-10 lg:px-18">
    <header class="dashboard-enter mx-auto grid max-w-255 grid-cols-[1fr_auto_1fr] items-center gap-4 border-t border-pitch-black pt-7">
        <a href="/#/" aria-label="Faiz home" class="h-10 w-24 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] sm:h-12 sm:w-32"></a>
        <Navbar />
        <div class="justify-self-end dashboard-enter"><ProfileDropdown /></div>
    </header>

    <div class="mx-auto mt-8 grid max-w-255 gap-8 lg:grid-cols-[minmax(0,3fr)_220px]">
        <section>
            <button type="button" class="button-lift flex w-full items-center justify-between rounded-full border-2 border-pitch-black bg-laser-pink px-8 py-4 font-archivo text-sm text-off-white shadow-[6px_6px_0_#000]" style="--button-complement: #00d9ff">
                <span class="mx-auto">Lagi penasaran sama apa nih?</span>
                <span aria-hidden="true" class="text-xl">⌕</span>
            </button>

            <div class="mt-8 space-y-7">
                {#each posts as post, index}
                    <div class="dashboard-enter" style="animation-delay: {index * 100}ms"><TimelinePost {...post} /></div>
                {/each}
            </div>
        </section>

        <aside class="dashboard-enter dashboard-enter-delay-1 space-y-8 lg:pt-5">
            <div class="min-h-70 border-2 border-pitch-black bg-[#48b3cf] p-5 shadow-[10px_10px_0_#000]">
                <h2 class="font-mono text-sm font-bold leading-relaxed">Leaderboard<br />Minggu ini 🏆</h2>
                <ol class="mt-8 space-y-4 font-mono text-xs font-bold">
                    {#each leaderboard as user, index}
                        <li class="flex justify-between border-b border-pitch-black/40 pb-2"><span>0{index + 1} {user}</span><span>{100 - index * 12}</span></li>
                    {/each}
                </ol>
            </div>
            <div class="h-28 border-2 border-pitch-black bg-[#48b3cf] shadow-[10px_10px_0_#000]"></div>
            <div class="h-28 border-2 border-pitch-black bg-[#48b3cf] shadow-[10px_10px_0_#000]"></div>
        </aside>
    </div>
</main>