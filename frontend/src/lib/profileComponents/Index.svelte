<script>
    export let photo = "";
    export let index = "";
    export let title = "";
    export let color = "";
</script>

<div class=" w-60 h-50 bg-{color} border-2 border-black shadow-[6px_6px_0_#000] flex flex-col justify-center items-center">
    <img src={photo} alt="" srcset="">
    <p class=" font-mono font-black text-md">{index}</p>
    <p class=" font-mono font-black text-xl">{title}</p>
</div>