<script>
	const achievements = ['Prestasi Menang Coding', 'Juara 1 WebTech GAYATAMA', 'Something'];
</script>

<div class="min-h-40 border-2 border-black bg-off-white p-4 shadow-[7px_7px_0_#000] sm:p-5">
	<h2 class="inline-block bg-pitch-black px-8 py-1 font-mono text-sm font-bold text-off-white">Prestasi</h2>
	<div class="mt-3 flex flex-wrap gap-2">
		{#each achievements as achievement, index}
			<span class="border-2 border-black px-3 py-1 font-mono text-xs font-bold shadow-[2px_2px_0_#000] {index === 0 ? 'bg-off-white' : 'bg-[#ffa174]'}">{achievement}</span>
		{/each}
	</div>
</div>