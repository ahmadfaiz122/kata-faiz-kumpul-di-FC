<script>
	/** @type {Array<{name: string, description?: string, levels?: string, tanggal_terbit?: number, kadaluwarsa?: number, certificate_path?: string}>} */
	export let achievements = [];

	function formatDate(value) {
		if (!value) return "-";
		return new Intl.DateTimeFormat("id-ID", { day: "2-digit", month: "2-digit", year: "numeric" }).format(new Date(value));
	}
</script>

<div class="min-h-32 border-2 border-black bg-off-white p-4 shadow-[6px_6px_0_#000] sm:p-5">
	<h2 class="inline-block bg-pitch-black px-8 py-1 font-mono text-sm font-bold text-off-white">Prestasi</h2>
	<div class="mt-3 space-y-3">
		{#if achievements.length === 0}
			<p class="font-mono text-xs">Belum ada prestasi.</p>
		{:else}
			{#each achievements as achievement}
				<article class="border-2 border-pitch-black bg-[#ffa174] p-3 shadow-[3px_3px_0_#000]">
					<div class="flex items-start justify-between gap-3"><h3 class="min-w-0 truncate font-archivo text-sm font-bold">{achievement.name}</h3>{#if achievement.levels}<span class="shrink-0 border border-pitch-black bg-[#ffe477] px-2 py-1 font-mono text-[9px] font-bold">{achievement.levels}</span>{/if}</div>
					{#if achievement.description}<p class="mt-2 line-clamp-3 whitespace-pre-line font-mono text-[10px] leading-relaxed">{achievement.description}</p>{/if}
					{#if achievement.tanggal_terbit}<p class="mt-1 font-mono text-[10px]">Terbit: {formatDate(achievement.tanggal_terbit)}</p>{/if}
					{#if achievement.certificate_path}
						<a href={achievement.certificate_path} target="_blank" rel="noreferrer" class="mt-3 inline-block border-2 border-pitch-black bg-off-white px-3 py-1 font-mono text-[10px] font-bold shadow-[2px_2px_0_#000]">Lihat sertifikat ↗</a>
					{/if}
				</article>
			{/each}
		{/if}
	</div>
</div>