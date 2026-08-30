<script>
    import Router from "svelte-spa-router";
    import Dashboard from "./assets/pages/Dashboard.svelte";
    import Profile from "./assets/pages/Profile.svelte";
    const routes = {
      "/" : Dashboard,
      "/profile" : Profile
    }
</script>

<Router {routes} />
