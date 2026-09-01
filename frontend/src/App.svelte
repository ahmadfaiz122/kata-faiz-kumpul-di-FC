<script>
    import Router from "svelte-spa-router";
    import Dashboard from "./pages/Dashboard.svelte";
    import Profile from "./pages/Profile.svelte";
    import Login from "./pages/Login.svelte";
    import Timeline from "./pages/Timeline.svelte";
    import Leaderboard from "./pages/Leaderboard.svelte";
    import EditProfile from "./pages/EditProfile.svelte";
    const routes = {
      "/" : Dashboard,
      "/timeline" : Timeline,
      "/leaderboard" : Leaderboard,
      "/profile" : Profile,
      "/login" : Login,
      "/edit-profile" : EditProfile,
    }
</script>

<Router {routes} />
