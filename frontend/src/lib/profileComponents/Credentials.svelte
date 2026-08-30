<script>
    const photo = 'https://i.pravatar.cc/150?img=12';
    const location = './assets/image.png';
</script>


<div class="  flex justify-between items-center">
    <div class=" flex">
        <div class=" border-2 w-40 h-40 border-black bg-off-white shadow-[6px_6px_0_#000]">
            <img src={photo} class=" h-full w-full" alt="">
        </div>
            <div class=" ml-5">
            <p class="w-max mb-3 px-2 py-1 font-medium text-medium border-2 bg-electric-cyan border-black shadow-[6px_6px_0_#000]">SELAMAT DATANG</p>
            <p class=" font-anton mb-3 text-black text-medium text-4xl ">KASTAMA SHOLEH ABI NUGRAHA</p>
            <div class=" flex gap-4">
                <img src={location} alt="" srcset="">
                <p class=" font-medium">State University Surabaya</p>
                <div class="w-max h-7 border border-black"></div>
                <div class=" flex justify-evenly gap-1 items-center ">
                    <div class=" w-4 h-4 rounded-full bg-white"></div>
                    <div class=" w-4 h-4 rounded-full bg-white"></div>
                    <div class=" w-4 h-4 rounded-full bg-white"></div>
                </div>
            
                <p class=" font-medium">(Social Media)</p>
            </div>
        </div>
    </div>
    
    <button type="button" title="button" class="font-mono font-bold p-4 mr-7 h-max w-max bg-off-white shadow-[6px_6px_0_#000]">Edit Profile</button>
</div>

