<script>
  /** @type {number|string|null} */
  export let id = null;
  export let duration = "2 HOUR";
  export let mentorName = "Reinzal";
  export let university = "University of Surabaya";
  export let instructor = "Svelte";
  export let category = "Design";
  export let credits = "2 kredit";
  export let ctaLabel = "Rekrut";

  /** @param {string} name */
  function firstName(name) {
    return name?.trim().split(/\s+/)[0] || "Anonymous";
  }

  /** @param {string} text */
  function textSizeClass(text) {
    const length = text?.length || 0;
    return length > 18 ? "text-extra-compact" : length > 12 ? "text-compact" : "";
  }

  /** @param {string} text */
  function skillFontSize(text) {
  const length = text?.length || 0;
  const maxSize = 40;
  const minSize = 15;
  const startShrinkAt = 3;   // chars before shrinking kicks in
  const fullyShrunkAt = 14;  // chars at which minSize is reached

  if (length <= startShrinkAt) return maxSize;
  if (length >= fullyShrunkAt) return minSize;

  const ratio = (length - startShrinkAt) / (fullyShrunkAt - startShrinkAt);
  return Math.round(maxSize - ratio * (maxSize - minSize));
}


  /** @param {unknown} value */
  function formatCredits(value) {
    return String(value ?? "").replace(/_/g, "").trim();
  }

  function recruit() {
    window.location.href = id ? `/#/rekrut/${id}` : "/#/rekrut";
  }

  /**
   * Fits a single-line label to the width of its parent.
   *
   * The font-size written in CSS is the maximum. If the text is wider than the
   * parent's content box, the font-size is reduced (never below `min`), and if
   * it still doesn't fit, the text is truncated with an ellipsis. It re-runs
   * whenever the parent is resized, the text changes, or the web font finishes
   * loading, so it works at any card width and for any text length.
   *
   * The parent must have a width that doesn't depend on the label itself
   * (a block, or a flex item with `flex: 1 1 0; min-width: 0`).
   *
   * @param {HTMLElement} node
   * @param {{ text?: string, min?: number }} params
   */
  function fit(node, params) {
    const parent = /** @type {HTMLElement} */ (node.parentElement);
    let options = params;
    let frame = 0;
    let lastWidth = -1;

    function run() {
      node.style.fontSize = "";
      node.style.maxWidth = "";
      node.style.overflow = "";
      node.style.textOverflow = "";

      const max = parseFloat(getComputedStyle(node).fontSize);
      const min = Math.min(options.min ?? 10, max);
      const box = getComputedStyle(parent);
      const available =
        parent.clientWidth - parseFloat(box.paddingLeft) - parseFloat(box.paddingRight);

      if (!(available > 0) || node.offsetWidth <= available) return;

      // Width scales roughly linearly with font-size: jump close, then nudge down.
      let size = Math.max(min, Math.floor((max * available) / node.offsetWidth));
      node.style.fontSize = `${size}px`;
      while (size > min && node.offsetWidth > available) {
        size -= 1;
        node.style.fontSize = `${size}px`;
      }

      if (node.offsetWidth > available) {
        node.style.maxWidth = `${available}px`;
        node.style.overflow = "hidden";
        node.style.textOverflow = "ellipsis";
      }
    }

    function schedule() {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(run);
    }

    const resizeObserver = new ResizeObserver(() => {
      if (parent.clientWidth === lastWidth) return;
      lastWidth = parent.clientWidth;
      schedule();
    });
    resizeObserver.observe(parent);
    document.fonts?.addEventListener("loadingdone", schedule);
    run();

    return {
      /** @param {{ text?: string, min?: number }} next */
      update(next) {
        options = next;
        schedule();
      },
      destroy() {
        cancelAnimationFrame(frame);
        resizeObserver.disconnect();
        document.fonts?.removeEventListener("loadingdone", schedule);
      },
    };
  }
</script>

<div class="card-wrap">
  <div class="card">

    <!-- LEFT SIDE -->
    <div class="left">
      <div class="banner-stack">
        <!-- clock badge -->
        <div class="clock" aria-hidden="true">
          <svg viewBox="0 0 24 24" class="clock-svg">
            <line x1="12" y1="12" x2="12" y2="6.5" stroke="black" stroke-width="2.4" stroke-linecap="round" />
            <line x1="12" y1="12" x2="16" y2="12" stroke="black" stroke-width="2.4" stroke-linecap="round" />
          </svg>
        </div>

        <!-- spark burst -->
        <svg class="burst" viewBox="0 0 40 60" fill="none" aria-hidden="true">
          <line x1="2" y1="52" x2="16" y2="30" stroke="black" stroke-width="4" stroke-linecap="round" />
          <line x1="0" y1="30" x2="20" y2="22" stroke="black" stroke-width="4" stroke-linecap="round" />
          <line x1="4" y1="10" x2="20" y2="14" stroke="black" stroke-width="4" stroke-linecap="round" />
        </svg>

        <div class="banner banner-pink">
          <span class="fit duration-text" use:fit={{ text: duration, min: 14 }}>{duration}</span>
        </div>
        <div class="banner banner-black">
          <span class="with-text">Learning</span>
        </div>
        <div class="banner banner-teal">
          <span class="fit name-text" title={instructor} use:fit={{ text: instructor, min: 14 }}>{instructor}</span>
        </div>
      </div>

      <!-- university line -->
      <div class="uni">
        <svg viewBox="0 0 24 24" class="uni-arrow" fill="none" aria-hidden="true">
          <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.4" stroke-linecap="round" />
          <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>{university}</span>
      </div>
    </div>

    <!-- DIVIDER -->
    <div class="divider" aria-hidden="true"></div>

    <!-- RIGHT SIDE -->
    <div class="right">
      <div class="box box-yellow">
        <div class="box-yellow-row">
          <span class="label-sm">Master</span>
          <svg viewBox="0 0 24 24" class="mini-arrow" fill="none" aria-hidden="true">
            <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.2" stroke-linecap="round" />
            <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <span class="fit label-lg" title={firstName(mentorName)} use:fit={{ text: firstName(mentorName), min: 10 }}>{firstName(mentorName)}</span>
      </div>

      <div class="box box-white">
        <div class="icon-tile icon-purple" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <polygon points="12,3 21,8 12,13 3,8" fill="black" stroke="black" stroke-width="1.4" />
            <polyline points="3,12 12,17 21,12" fill="none" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
            <polyline points="3,16 12,21 21,16" fill="none" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <div class="box-text-col">
          <span class="label-sm">Category</span>
          <span class="fit label-lg" title={category} use:fit={{ text: category, min: 10 }}>{category}</span>
        </div>
      </div>

      <div class="box box-white">
        <div class="icon-tile icon-teal" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="8" fill="none" stroke="black" stroke-width="2" />
            <path d="M12 7V17M15 9.5C14.2 8.8 13.2 8.5 12 8.5c-1.7 0-3 0.8-3 2s1.3 2 3 2 3 0.8 3 2-1.3 2-3 2c-1.2 0-2.2-0.3-3-1" stroke="black" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </div>
        <div class="box-text-col">
          <span class="fit label-lg credit-value" title={formatCredits(credits)} use:fit={{ text: formatCredits(credits), min: 9 }}>{formatCredits(credits)}</span>
        </div>
      </div>

      <button type="button" class="box box-cta" onclick={recruit}>
        <span class="cta-slot">
          <span class="fit cta-label" use:fit={{ text: ctaLabel, min: 10 }}>{ctaLabel}</span>
        </span>
        <svg viewBox="0 0 24 24" class="cta-arrow" fill="none" aria-hidden="true">
          <line x1="5" y1="19" x2="19" y2="5" stroke="black" stroke-width="2.6" stroke-linecap="round" />
          <polyline points="8,5 19,5 19,16" fill="none" stroke="black" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
  </div>
</div>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap');

  .card-wrap {
    container-type: inline-size;
    container-name: skillcard;
    width: 100%;
    height: 100%; 
  }

  .card-wrap :global(*) {
    box-sizing: border-box;
  }

  .card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
    height: 100%;
    padding: 16px 16px 22px;
    background: #EDEAE2;
    border: 3px solid #0A0A0A;
    border-radius: 26px;
    box-shadow: 9px 9px 0 0 #0A0A0A;
    overflow: hidden;
    font-family: 'Silkscreen', monospace;
    color: #0A0A0A;
  }

  .fit {
    display: block;
    flex: none;
    width: max-content;
    white-space: nowrap;
  }

  .left {
    container-type: inline-size;
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 0;
  }

  .banner-stack {
    position: relative;
    max-width: 340px;
    margin: 24px 6px 0 clamp(16px, 8cqw, 24px);
  }

  .clock {
    position: absolute;
    top: -11px;
    left: -9px;
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
    top: -18px;
    right: 7px;
    width: 34px;
    height: 52px;
    z-index: 20;
  }

  .banner {
    position: relative;
    display: flex;
    align-items: center;
    border: 3px solid #0A0A0A;
    box-shadow: 5px 5px 0 0 #0A0A0A;
  }

  .banner-pink {
    background: #E8225A;
    transform: skewX(-9deg) rotate(-1deg);
    padding: clamp(9px, 5cqw, 14px) 34px clamp(9px, 5cqw, 14px) clamp(14px, 7cqw, 20px);
    z-index: 12;
  }
  .duration-text {
    transform: skewX(9deg);
    color: #F3EFE6;
    font-size: 42px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 1px;
    text-shadow: 4px 4px 0 #0A0A0A;
    padding-right: 4px; 
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
    padding: clamp(10px, 5.5cqw, 16px) clamp(16px, 8.5cqw, 26px);
    margin-top: -6px;
    z-index: 10;
  }
  .name-text {
    transform: skewX(9deg);
    color: #0A0A0A;
    font-size: 40px;
    font-weight: 700;
    line-height: 1;
    text-shadow: 3px 3px 0 rgba(10, 10, 10, 0.25);
    padding-right: 3px;
  }

  .uni {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    margin-top: auto;
    padding-left: 4px;
    min-width: 0;
  }
  .uni-arrow { width: 15px; height: 15px; flex: none; }
  .uni span {
    min-width: 0;
    font-size: 12px;
    line-height: 1.3;
    color: #0A0A0A;
    overflow-wrap: anywhere;
  }

  .divider {
    flex: none;
    height: 2px;
    background: #0A0A0A;
    opacity: 0.85;
  }

  .right {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 9px;
    min-width: 0;
  }

  .box {
    min-width: 0;
    border: 2.5px solid #0A0A0A;
    border-radius: 10px;
    box-shadow: 4px 4px 0 0 #0A0A0A;
    background: #F3EFE6;
  }

  .box-yellow {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px 14px;
    background: #F5C518;
  }
  .box-yellow-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .mini-arrow { width: 16px; height: 16px; flex: none; }

  .box-white {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 10px 14px;
  }
  .box-text-col {
    flex: 1 1 0;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .icon-tile {
    flex: none;
    width: 32px;
    height: 32px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
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
  .credit-value { font-size: 13px; }

  .box-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-height: 48px;
    padding: 10px 14px;
    background: #E8225A;
    color: #000000;
    font-family: inherit;
    text-align: left;
    cursor: pointer;
    transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
  }
  .cta-slot {
    flex: 1 1 0;
    min-width: 0;
  }
  .cta-label {
    font-size: 14px;
    font-weight: 700;
  }
  .cta-arrow { width: 18px; height: 18px; flex: none; }

  .box-cta:focus-visible {
    outline: 3px solid #0A0A0A;
    outline-offset: 3px;
  }
  .box-cta:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0 0 #0A0A0A;
  }
  @media (hover: hover) {
    .box-cta:hover {
      transform: translateY(-2px);
      box-shadow: 5px 5px 0 0 #0A0A0A;
      filter: brightness(1.05);
    }
  }
  @media (prefers-reduced-motion: reduce) {
    .box-cta { transition: none; }
  }

  @container skillcard (min-width: 380px) and (max-width: 519.98px) {
    .right {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @container skillcard (min-width: 520px) {
    .card {
      flex-direction: row;
      gap: 0;
      min-height: 281px;
      padding: 18px 16px 26px;
    }

    .left {
      flex: 1 1 0;
    }

    .divider {
      width: 2px;
      height: auto;
      margin: 4px 18px;
    }

    .right {
      flex: 0 0 clamp(190px, 38cqw, 230px);
      grid-template-rows: auto minmax(48px, 1fr) auto auto;
    }
  }

  @media (max-width: 380px) {
    .card {
      padding-left: 12px;
      padding-right: 12px;
    }

    .banner-stack {
      transform: scale(0.7);
    }

    .left {
      min-height: 188px;
    }

    .bottom-actions {
      grid-template-columns: 1fr;
    }
  }

</style>