<script>
  export let duration = "2 HOUR";
  export let mentor = "Reinzal";
  export let skill = "Svelte Anjay";
  export let category = "Design";
  export let university = "University of Surabaya";
  export let credit = 2;
</script>

<article class="dashboard-enter button-lift grid min-h-69.5 w-full max-w-145 grid-cols-2 rounded-[28px] border-4 border-pitch-black bg-off-white px-6 py-6 font-archivo shadow-[7px_7px_0_#000] sm:px-8" style="--button-complement: #ccff00">
  <div class="flex flex-col justify-between">
    <div>
      <div
        class=" w-9 h-9 rounded-full bg-[#713b4f] flex items-center justify-center mb-7">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 3"/>
        </svg>
      </div>

      <h2 class="font-anton text-laser-pink text-[42px] leading-none">
        {duration}
      </h2>

      <!-- Mentor -->
      <div class="flex items-center gap-2 mt-2">
        <span class="text-[17px] font-mono font-medium text-pitch-black">
          with
        </span>

        <span
          class=" text-electric-cyan text-[30px] font-semibold leading-none font-mono">
          {mentor}
        </span>
      </div>
    </div>


    <!-- University -->
    <div
      class=" flex items-center gap-2 text-pitch-black/40 text-[16px]">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 19L18 7M18 7v8M18 7H10"/>
      </svg>
      <span>{university}</span>
    </div>

  </div>


  <!-- RIGHT SIDE -->
  <div class="flex flex-col justify-between pl-3">

    <!-- Skill Information -->
    <div>
      <p
        class="
          text-pale-red
          font-semibold
          text-[16px]
          mb-2 font-mono
        "
      >
        You can learn from {mentor}:
      </p>

      <h3
        class="
          text-[#34476A]
          font-bold
          text-[26px]
          leading-tight
          font-mono
        "
      >
        {skill}
      </h3>

      <p
        class="
          text-pale-red
          font-semibold
          text-[16px]
          mt-6
          mb-2
        "
      >
        From Category:
      </p>

      <p
        class="
          text-[#34476A]
          font-bold
          text-[25px]
          leading-none
        "
      >
        {category}
      </p>
    </div>


    <!-- Bottom Right -->
    <div class="flex items-center justify-between gap-4">

      <span
        class="
          text-pitch-black
          text-[16px]
          whitespace-nowrap
        "
      >
        {credit} kredit
      </span>

      <button
        class="button-lift
          bg-pitch-black
          text-off-white
          font-bold
          text-[23px]

          px-8 py-2
          rounded-[9px]

        "
        style="--button-complement: #00d9ff"
      >
        Rekrut
      </button>

    </div>

  </div>
</article>