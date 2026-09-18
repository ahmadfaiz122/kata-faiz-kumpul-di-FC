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
<div class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] sm:gap-4 sm:px-6 sm:py-3">
    <img src={user.avatar} alt={user.name} class="h-10 w-10 rounded-full border-2 border-black object-cover">
    <span class="font-mono text-sm font-bold md:text-xl md:font-normal">{firstName(user.name)}</span>
</div>
{/if}
