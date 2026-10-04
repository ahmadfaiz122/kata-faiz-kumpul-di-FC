<script>
    import { onMount } from "svelte";
    import LandingPage from "./LandingPage.svelte";
    import Timeline from "./Timeline.svelte";

    let loggedIn = $state(typeof window !== "undefined" && Boolean(localStorage.getItem("auth_token")));

    onMount(() => {
        loggedIn = Boolean(localStorage.getItem("auth_token"));
    });
</script>

{#if loggedIn}
    <Timeline />
{:else}
    <LandingPage />
{/if}
