<script>
    import { onMount } from "svelte";
    import indexImage1 from "../assets/star.png";
    import indexImage2 from "../assets/stats.png";
    import indexImage3 from "../assets/rank.png";
    import Bio from "../lib/profileComponents/Bio.svelte";
    import Credentials from "../lib/profileComponents/Credentials.svelte";
    import Index from "../lib/profileComponents/Index.svelte";
    import Achievement from "../lib/profileComponents/Achievement.svelte";
    import Navbar from "../lib/Navbar.svelte";
    import ProfileDropdown from "../lib/ProfileDropdown.svelte";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let user = null;
    let loading = true;
    let error = "";

    onMount(async () => {
        const token = localStorage.getItem("auth_token");

        if (!token) {
            window.location.href = "/#/login";
            return;
        }

        try {
            const response = await fetch(`${backendUrl}/api/user`, {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                },
            });

            if (response.status === 401) {
                localStorage.removeItem("auth_token");
                window.location.href = "/#/login";
                return;
            }

            if (!response.ok) {
                throw new Error("Gagal mengambil data profile.");
            }

            user = await response.json();
        } catch (requestError) {
            error = requestError.message;
        } finally {
            loading = false;
        }
    });
</script>

<main class="min-h-screen overflow-hidden px-5 py-7 sm:px-10 lg:px-14">
    <header class="dashboard-enter mx-auto grid max-w-320 grid-cols-[1fr_auto_1fr] items-center gap-4">
        <a href="/#/" aria-label="Faiz home" class="h-11 w-28 border-2 border-pitch-black bg-off-white shadow-[5px_5px_0_#000] transition-transform hover:-translate-y-1 sm:h-14 sm:w-36"></a>
        <Navbar />
        <div class="justify-self-end">
            <ProfileDropdown />
        </div>
    </header>
    <div class="mx-auto mt-14 max-w-320">
        <div class="dashboard-enter relative z-100">
            {#if loading}
                <p class="border-y border-pitch-black py-10 text-center font-mono text-sm">Memuat profile...</p>
            {:else if error}
                <p class="border-y border-pitch-black py-10 text-center font-mono text-sm text-laser-pink">{error}</p>
            {:else}
                <Credentials user={user} />
            {/if}
        </div>

        {#if user}
        <section class="dashboard-enter dashboard-enter-delay-1 mt-7 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <Bio user={user} />
            <div class="grid gap-5 sm:grid-cols-3">
                <Index photo={indexImage1} index="4.5/5.0" title="Rating" color="neon-yellow" />
                <Index photo={indexImage2} index="100" title="Reputation" color="laser-pink" />
                <Index photo={indexImage3} index="10000/10000" title="Leaderboard Rank" color="electric-cyan" />
            </div>
        </section>

        <section class="dashboard-enter dashboard-enter-delay-2 mt-5 grid gap-7 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <div class="flex min-h-40 flex-col justify-center bg-[#ffa174] px-8 py-7 shadow-[10px_10px_0_#000]">
                <span class="font-archivo text-6xl leading-none text-[#3d6cff] h-[30px] mb-3">“</span>
                <p class="font-mono text-sm font-bold">You can also call me by</p>
                <p class="mt-1 font-anton text-3xl uppercase">{user.profile?.alias || user.profile?.username || user.name}</p>
            </div>
            <Achievement achievements={user.profile?.achievements || []} />
        </section>

        <section class="dashboard-enter dashboard-enter-delay-3 mt-7 border-2 border-pitch-black bg-off-white p-5 shadow-[7px_7px_0_#000]">
            <h2 class="inline-block bg-pitch-black px-10 py-1 font-mono text-sm font-bold text-off-white">Skills</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                {#each user.profile?.skills || [] as skill}
                    <span class="border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-sm shadow-[3px_3px_0_#000]">{skill}</span>
                {/each}
            </div>
        </section>
        {/if}
    </div>
</main>