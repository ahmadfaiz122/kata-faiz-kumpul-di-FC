<script>
    import Navbar from "../../lib/Navbar.svelte";
    import CategoryBar from "../../lib/CategoryBar.svelte";
    import ProfileDropdown from "../../lib/ProfileDropdown.svelte";
</script>

<div class=" flex justify-around bg-amber-400 mt-5">
    <ProfileDropdown />
    <Navbar />
    <ProfileDropdown />
</div>

<CategoryBar />