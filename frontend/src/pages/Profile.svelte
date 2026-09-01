<script>
    import indexImage1 from "../assets/star.png";
    import indexImage2 from "../assets/stats.png";
    import indexImage3 from "../assets/rank.png";
    import Bio from "../lib/profileComponents/Bio.svelte";
    import Credentials from "../lib/profileComponents/Credentials.svelte";
    import Index from "../lib/profileComponents/Index.svelte";
    import Achievement from "../lib/profileComponents/Achievement.svelte";
</script>

<main class="min-h-screen overflow-hidden px-5 py-7 sm:px-10 lg:px-14">
    <div class="mx-auto max-w-320">
        <div class="dashboard-enter relative z-100">
            <Credentials />
        </div>

        <section class="dashboard-enter dashboard-enter-delay-1 mt-7 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <Bio />
            <div class="grid gap-5 sm:grid-cols-3">
                <Index photo={indexImage1} index="4.5/5.0" title="Rating" color="neon-yellow" />
                <Index photo={indexImage2} index="100" title="Reputation" color="laser-pink" />
                <Index photo={indexImage3} index="10000/10000" title="Leaderboard Rank" color="electric-cyan" />
            </div>
        </section>

        <section class="dashboard-enter dashboard-enter-delay-2 mt-5 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <div class="flex min-h-40 flex-col justify-center bg-[#ffa174] px-8 py-7 shadow-[10px_10px_0_#000]">
                <span class="font-archivo text-6xl leading-none text-[#3d6cff]">“</span>
                <p class="font-mono text-sm font-bold">You can also call me by</p>
                <p class="mt-1 font-anton text-3xl uppercase">Kastama</p>
            </div>
            <Achievement />
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]">
            <h2 class="inline-block bg-pitch-black px-10 py-1 font-mono text-sm font-bold text-off-white">Skills</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <span class="border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-sm shadow-[3px_3px_0_#000]">Javascript</span>
                <span class="border-2 border-pitch-black bg-[#ffa174] px-3 py-1 font-mono text-sm shadow-[3px_3px_0_#000]">Figma</span>
            </div>
        </section>
    </div>
</main>