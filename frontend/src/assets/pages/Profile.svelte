<script>
    import Bio from "../../lib/profileComponents/Bio.svelte";
    import Credentials from "../../lib/profileComponents/Credentials.svelte";
    import Index from "../../lib/profileComponents/Index.svelte";
    import indexImage1 from "../../assets/star.png";
    import indexImage2 from "../../assets/stats.png";
    import indexImage3 from "../../assets/rank.png";
    import Achievement from "../../lib/profileComponents/Achievement.svelte";
    
</script>

<div>
    <Credentials />
    <div class=" flex gap-7 py-5 px-9 justify-between">
        <Bio />
        <Index 
            photo={indexImage1}
            index="4.5/5"
            title="Rating"
            color="neon-yellow"
        />
        <Index 
            photo={indexImage2}
            index="100"
            title="Reputation"
            color="laser-pink"
        />
        <Index 
            photo={indexImage3}
            index="1000/1000"
            title="Leaderboard Rank"
            color="electric-cyan"
        />
    </div>
    <Achievement />
</div>