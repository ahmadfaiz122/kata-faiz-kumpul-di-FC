<script>
    import Router from "svelte-spa-router";
    import Dashboard from "./assets/pages/Dashboard.svelte";
    import Profile from "./assets/pages/Profile.svelte";
    import Login from "./assets/pages/Login.svelte";
    const routes = {
      "/" : Dashboard,
      "/profile" : Profile,
      "/login" : Login,
    }
</script>

<Router {routes} />
