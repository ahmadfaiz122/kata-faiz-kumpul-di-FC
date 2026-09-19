<script>
    import { onMount } from "svelte";

    const backendUrl = import.meta.env.VITE_API_URL || "http://localhost:8000";
    let user = null;

    function firstName(name) {
        return name?.trim().split(/\s+/)[0] || "";
    }

    onMount(async () => {
        const token = localStorage.getItem("auth_token");
        if (!token) return;

        const response = await fetch(`${backendUrl}/api/user`, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${token}`,
            },
        });

        if (response.ok) user = await response.json();
    });
</script>

{#if user}
<div class="flex items-center gap-3">
    <div class="flex items-center gap-2 rounded-full border-2 border-black bg-cyber-lime px-3 py-2 shadow-[6px_6px_0_#000] sm:gap-3 sm:px-5 sm:py-3" title="Kredit kamu">
        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none">
            <ellipse cx="12" cy="6" rx="7" ry="3" fill="none" stroke="black" stroke-width="2" />
            <path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6" fill="none" stroke="black" stroke-width="2" />
            <path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" fill="none" stroke="black" stroke-width="2" />
        </svg>
        <span class="font-mono text-sm font-bold md:text-base">{user.profile?.credits ?? 0}</span>
    </div>

    <a href="/#/edit-profile" aria-label="Edit profile" title="Edit profile" class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black sm:gap-4 sm:px-6 sm:py-3">
        <img src={user.avatar} alt={user.name} class="h-10 w-10 rounded-full border-2 border-black object-cover">
        <span class="font-mono text-sm font-bold md:text-xl md:font-normal">{firstName(user.name)}</span>
    </a>
</div>
{/if}
