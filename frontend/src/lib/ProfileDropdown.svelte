<script context="module">
    let cachedToken = null;
    let cachedUser = null;
    let userRequest = null;

    async function loadUser(token, backendUrl) {
        if (cachedToken === token && cachedUser) return cachedUser;
        if (cachedToken === token && userRequest) return userRequest;

        cachedToken = token;
        userRequest = fetch(`${backendUrl}/api/user`, {
            headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                cachedUser = data;
                return data;
            })
            .finally(() => {
                userRequest = null;
            });

        return userRequest;
    }
</script>

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

        user = await loadUser(token, backendUrl);
    });
</script>

{#if user}
<div class="flex flex-nowrap items-center justify-end gap-1.5 lg:gap-3">
    <a href="/#/messages" aria-label="Messages" title="Messages" class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-black bg-off-white shadow-[3px_3px_0_#000] transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black lg:h-11 lg:w-11 sm:shadow-[5px_5px_0_#000]">
        <svg viewBox="0 0 24 24" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" aria-hidden="true">
            <path d="M5 6.5A2.5 2.5 0 0 1 7.5 4h9A2.5 2.5 0 0 1 19 6.5v6a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4.15A2.5 2.5 0 0 1 5 12.5v-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
            <path d="m8 8 4 3 4-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full border-2 border-black bg-laser-pink px-1 font-mono text-[8px] font-bold lg:h-5 lg:min-w-5 sm:text-[9px]">1</span>
    </a>
    <div class="flex shrink-0 items-center gap-1 rounded-full border-2 border-black bg-cyber-lime px-2 py-1.5 shadow-[3px_3px_0_#000] sm:gap-3 sm:px-5 sm:py-3 sm:shadow-[6px_6px_0_#000]" title="Kredit kamu">
        <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 md:h-5 md:w-5" fill="none">
            <ellipse cx="12" cy="6" rx="7" ry="3" fill="none" stroke="black" stroke-width="2" />
            <path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6" fill="none" stroke="black" stroke-width="2" />
            <path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" fill="none" stroke="black" stroke-width="2" />
        </svg>
        <span class="font-mono text-xs font-bold sm:text-sm md:text-base">{user.profile?.credits ?? 0}</span>
    </div>

    <a href="/#/profile" aria-label="Buka profile" title="Buka profile" class="flex items-center gap-2 rounded-full border-2 border-black bg-electric-cyan px-3 py-2 shadow-[6px_6px_0_#000] transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black sm:gap-4 sm:px-6 sm:py-3">
        <img src={user.avatar} alt={user.name} class="h-10 w-10 rounded-full border-2 border-black object-cover">
        <span class="font-mono text-sm font-bold md:text-xl md:font-normal">{firstName(user.name)}</span>
    </a>
</div>
{:else}
<a href="/#/login" class="button-lift border-2 border-black bg-laser-pink px-5 py-3 font-mono text-sm font-bold text-black shadow-[5px_5px_0_#000] transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">Login</a>
{/if}

<style>
    @media (max-width: 767px) {
        .profile-actions {
            gap: 0.25rem;
            max-width: 100%;
        }

        .profile-message,
        .profile-credit,
        .profile-user {
            height: 2.75rem;
            width: 2.75rem;
        }

        .profile-credit,
        .profile-user {
            justify-content: center;
            padding: 0;
        }

        .profile-credit svg,
        .profile-user span {
            display: none;
        }

        .profile-user img {
            height: 2.15rem;
            width: 2.15rem;
        }
    }
</style>
