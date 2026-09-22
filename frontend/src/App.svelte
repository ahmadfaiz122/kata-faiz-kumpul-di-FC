<script>
    import Router from "svelte-spa-router";
    import Dashboard from "./pages/Dashboard.svelte";
    import Profile from "./pages/Profile.svelte";
    import Login from "./pages/Login.svelte";
    import Timeline from "./pages/Timeline.svelte";
    import Leaderboard from "./pages/Leaderboard.svelte";
    import EditProfile from "./pages/EditProfile.svelte";
    import AddProposal from "./pages/AddProposal.svelte";
    import Rekrut from "./pages/Rekrut.svelte";
    import Messages from "./pages/Messages.svelte";
    import LandingPage from "./pages/LandingPage.svelte";
    import Transaksi from "./pages/Transaksi.svelte";
    const routes = {
      "/" : Timeline,
      "/swapp" : Dashboard,
      "/timeline" : Timeline,
      "/leaderboard" : Leaderboard,
      "/profile" : Profile,
      "/login" : Login,
      "/edit-profile" : EditProfile,
      "/add-proposal" : AddProposal,
      "/rekrut/:id" : Rekrut,
      "/rekrut" : Rekrut,
      "/messages" : Messages,
      "/landing" : LandingPage,
      "/transaksi" : Transaksi,
    }
</script>

<Router {routes} />
