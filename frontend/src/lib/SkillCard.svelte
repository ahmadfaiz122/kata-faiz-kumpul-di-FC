<script>
  // Content is exposed as props so the card stays reusable,
  // but defaults reproduce the reference image exactly.
  export let duration = "2 HOUR";
  export let mentorName = "Reinzal";
  export let university = "University of Surabaya";
  export let instructor = "Svelte";
  export let category = "Design";
  export let credits = "2 kredit";
  export let ctaLabel = "Rekrut";
</script>

<div class="card">
  <!-- LEFT SIDE -->
  <div class="left">
    <!-- clock badge -->
    <div class="clock">
      <svg viewBox="0 0 24 24" class="clock-svg">
        <line x1="12" y1="12" x2="12" y2="6.5" stroke="black" stroke-width="2.4" stroke-linecap="round" />
        <line x1="12" y1="12" x2="16" y2="12" stroke="black" stroke-width="2.4" stroke-linecap="round" />
      </svg>
    </div>

    <!-- spark burst -->
    <svg class="burst" viewBox="0 0 40 60" fill="none">
      <line x1="2" y1="52" x2="16" y2="30" stroke="black" stroke-width="4" stroke-linecap="round" />
      <line x1="0" y1="30" x2="20" y2="22" stroke="black" stroke-width="4" stroke-linecap="round" />
      <line x1="4" y1="10" x2="20" y2="14" stroke="black" stroke-width="4" stroke-linecap="round" />
    </svg>

    <!-- banner stack -->
    <div class="banner-stack">
      <div class="banner banner-pink">
        <span class="duration-text">{duration}</span>
      </div>
      <div class="banner banner-black">
        <span class="with-text">Learning</span>
      </div>
      <div class="banner banner-teal">
        <span class="name-text">{instructor}</span>
      </div>
    </div>

    <!-- university line -->
    <div class="uni">
      <svg viewBox="0 0 24 24" class="uni-arrow" fill="none">
        <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.4" stroke-linecap="round" />
        <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span>{university}</span>
    </div>
  </div>

  <!-- DIVIDER -->
  <div class="divider"></div>

  <!-- RIGHT SIDE -->
  <div class="right">
    <div class="box box-yellow">
      <div class="box-yellow-row">
        <span class="label-sm">You can learn from</span>
        <svg viewBox="0 0 24 24" class="mini-arrow" fill="none">
          <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.2" stroke-linecap="round" />
          <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <span class="label-lg">{mentorName}</span>
    </div>

    <div class="box box-white">
      <div class="icon-tile icon-purple">
        <svg viewBox="0 0 24 24" fill="none">
          <polygon points="12,3 21,8 12,13 3,8" fill="black" stroke="black" stroke-width="1.4" />
          <polyline points="3,12 12,17 21,12" fill="none" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
          <polyline points="3,16 12,21 21,16" fill="none" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div class="box-text-col">
        <span class="label-sm">From Category:</span>
        <span class="label-lg">{category}</span>
      </div>
    </div>

    <div class="box box-white">
      <div class="icon-tile icon-teal">
        <svg viewBox="0 0 24 24" fill="none">
          <ellipse cx="12" cy="6" rx="7" ry="3" fill="none" stroke="black" stroke-width="2" />
          <path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6" fill="none" stroke="black" stroke-width="2" />
          <path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" fill="none" stroke="black" stroke-width="2" />
        </svg>
      </div>
      <span class="label-lg">{credits}</span>
    </div>

    <button class="box box-cta">
      <span>{ctaLabel}</span>
      <svg viewBox="0 0 24 24" class="cta-arrow" fill="none">
        <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.6" stroke-linecap="round" />
        <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
  </div>
</div>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap');

  :global(.card), .card * {
    font-family: 'Silkscreen', monospace;
    box-sizing: border-box;
  }

  .card {
    position: relative;
    width: 580px;
    height: 281px;
    background: #EDEAE2;
    border: 3px solid #0A0A0A;
    border-radius: 26px;
    box-shadow: 9px 9px 0 0 #0A0A0A;
    display: flex;
    padding: 18px 20px;
    overflow: hidden;
  }

  /* ---------- LEFT ---------- */
  .left {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
  }

  .clock {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 33px;
    height: 33px;
    border-radius: 999px;
    background: #F5C518;
    border: 3px solid #0A0A0A;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 30;
  }
  .clock-svg { width: 26px; height: 26px; }

  .burst {
    position: absolute;
    top: 8px;
    right: 14px;
    width: 34px;
    height: 52px;
    z-index: 20;
  }

  .banner-stack {
    position: absolute;
    top: 26px;
    left: 24px;
    width: 260px;
  }

  .banner {
    border: 3px solid #0A0A0A;
    display: flex;
    align-items: center;
    box-shadow: 5px 5px 0 0 #0A0A0A;
    position: relative;
  }

  .banner-pink {
    background: #E8225A;
    transform: skewX(-9deg) rotate(-1deg);
    padding: 14px 34px 14px 20px;
    z-index: 12;
  }
  .duration-text {
    display: inline-block;
    transform: skewX(9deg);
    color: #F3EFE6;
    font-size: 42px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 1px;
    text-shadow: 4px 4px 0 #0A0A0A;
    white-space: nowrap;
  }

  .banner-black {
    background: #0A0A0A;
    transform: skewX(-9deg) rotate(-1deg);
    padding: 6px 22px 6px 14px;
    margin-top: -14px;
    margin-left: -2px;
    width: fit-content;
    z-index: 14;
  }
  .with-text {
    display: inline-block;
    transform: skewX(9deg);
    color: #F3EFE6;
    font-size: 16px;
    font-weight: 400;
    white-space: nowrap;
  }

  .banner-teal {
    background: #3FD6C4;
    transform: skewX(-9deg) rotate(-1deg);
    padding: 16px 26px;
    margin-top: -6px;
    z-index: 10;
  }
  .name-text {
    display: inline-block;
    transform: skewX(9deg);
    color: #0A0A0A;
    font-size: 40px;
    font-weight: 700;
    line-height: 1;
    text-shadow: 3px 3px 0 rgba(10,10,10,0.25);
    white-space: nowrap;
  }

  .uni {
    position: absolute;
    bottom: 2px;
    left: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .uni-arrow { width: 15px; height: 15px; }
  .uni span { font-size: 12px; color: #0A0A0A; }

  /* ---------- DIVIDER ---------- */
  .divider {
    width: 2px;
    background: #0A0A0A;
    margin: 4px 18px;
    opacity: 0.85;
  }

  /* ---------- RIGHT ---------- */
  .right {
    flex: 0 0 205px;
    display: flex;
    flex-direction: column;
    gap: 9px;
  }

  .box {
    border: 2.5px solid #0A0A0A;
    border-radius: 10px;
    box-shadow: 4px 4px 0 0 #0A0A0A;
    background: #F3EFE6;
  }

  .box-yellow {
    background: #F5C518;
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .box-yellow-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .mini-arrow { width: 16px; height: 16px; flex-shrink: 0; }

  .box-white {
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
  }
  .box-text-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .icon-tile {
    width: 32px;
    height: 32px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .icon-tile svg { width: 18px; height: 18px; }
  .icon-purple { background: #8B7BF0; }
  .icon-teal { background: #3FD6C4; }

  .label-sm {
    font-size: 10px;
    color: #0A0A0A;
    letter-spacing: 0.3px;
  }
  .label-lg {
    font-size: 17px;
    font-weight: 700;
    color: #0A0A0A;
    line-height: 1.15;
  }

  .box-cta {
    background: #E8225A;
    color: #000000;
    font-size: 20px;
    font-weight: 700;
    padding: 5px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    border-radius: 10px;
    transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
  }
  .box-cta:hover {
    transform: translateY(-2px);
    box-shadow: 5px 5px 0 0 #0A0A0A;
    filter: brightness(1.05);
  }
  .box-cta:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0 0 #0A0A0A;
  }
  .cta-arrow { width: 22px; height: 22px; }
</style>