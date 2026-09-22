<script>
    import { scale } from "svelte/transition";
    import { cubicOut } from "svelte/easing";

    /**
     * @typedef {{ key: string, label: string, rangeLabel: string, min: number, max: number, step: number, low: number, high: number, icon: "star" | "shield" | "credit" }} FilterRange
     */

    let {
        open = $bindable(false),
        triggerEl = null,
        onApply = () => {},
        onReset = () => {}
    } = $props();

    const defaults = [
        { key: "rating", label: "Rating", rangeLabel: "0.0 - 5.0", min: 0, max: 5, step: 0.1, low: 1.5, high: 4.5, icon: "star" },
        { key: "reputasi", label: "Reputasi", rangeLabel: "0 - 100", min: 0, max: 100, step: 1, low: 50, high: 100, icon: "shield" },
        { key: "credit", label: "Credit", rangeLabel: "1 - 100", min: 1, max: 100, step: 1, low: 10, high: 80, icon: "credit" }
    ];

    /** @type {FilterRange[]} */
    let filters = $state(defaults.map((f) => ({ ...f })));

    let panelEl = $state(null);

    function clamp(v, lo, hi) {
        return Math.min(hi, Math.max(lo, v));
    }

    function round(v, step) {
        const decimals = step < 1 ? 1 : 0;
        return Number(v.toFixed(decimals));
    }

    function pct(value, f) {
        return ((value - f.min) / (f.max - f.min)) * 100;
    }

    function display(f, v) {
        return f.step < 1 ? v.toFixed(1) : String(Math.round(v));
    }

    function onLowInput(f) {
        if (f.low > f.high) f.low = f.high;
    }
    function onHighInput(f) {
        if (f.high < f.low) f.high = f.low;
    }

    function nudgeLow(f, dir) {
        f.low = clamp(round(f.low + dir * f.step, f.step), f.min, f.high);
    }
    function nudgeHigh(f, dir) {
        f.high = clamp(round(f.high + dir * f.step, f.step), f.low, f.max);
    }

    function resetFilters() {
        filters = defaults.map((f) => ({ ...f, low: f.min, high: f.max }));
        onReset();
    }

    function applyFilters() {
        onApply(filters.map(({ key, low, high }) => ({ key, low, high })));
        open = false;
    }
    function thumbPct(value, f) {
    const p = pct(value, f);
    const thumb = 22;
    return `calc(${thumb / 2}px + (100% - ${thumb}px) * ${p / 100})`;
}

    // Close the dropdown when clicking anything outside the panel and its trigger.
    $effect(() => {
        if (!open) return;

        function handlePointerDown(event) {
            const target = event.target;
            if (panelEl && panelEl.contains(target)) return;
            if (triggerEl && triggerEl.contains(target)) return;
            open = false;
        }

        window.addEventListener("pointerdown", handlePointerDown);
        return () => window.removeEventListener("pointerdown", handlePointerDown);
    });

    // Reusable Tailwind fragments (kept as constants so the markup below stays readable).
    const chamferSm = "[clip-path:polygon(0_0,calc(100%-8px)_0,100%_8px,100%_100%,8px_100%,0_calc(100%-8px))]";
    const chamferBtn = "[clip-path:polygon(0_0,calc(100%-12px)_0,100%_12px,100%_100%,12px_100%,0_calc(100%-12px))]";
    const bubbleClass =
        "pointer-events-none absolute -top-7 -translate-x-1/2 whitespace-nowrap rounded-md bg-black px-2 py-[3px] text-[11px] font-bold text-[#f3efe6] " +
        "after:absolute after:left-1/2 after:bottom-[-3px] after:h-[7px] after:w-[7px] after:-translate-x-1/2 after:rotate-45 after:bg-black after:content-['']";
    const spinBtnClass = "flex h-3 w-4 items-center justify-center text-black/75 hover:text-black";
    const btnBase =
        "flex-1 border-[2.5px] border-black py-[11px] text-sm font-bold shadow-[4px_4px_0_0_#0a0a0a] transition-transform duration-150 " +
        "hover:-translate-x-[2px] hover:-translate-y-[2px] hover:shadow-[6px_6px_0_0_#0a0a0a] active:translate-x-0 active:translate-y-0 active:shadow-[2px_2px_0_0_#0a0a0a] ";
</script>

{#if open}
    <div class="absolute top-full left-0 right-0 z-40 mt-[18px] w-full sm:left-auto sm:w-[420px]">

        <div
            bind:this={panelEl}
            class="relative z-[2] flex max-h-[480px] flex-col rounded-[26px] border-[3px] border-black bg-[#ede9e0] px-5 pb-4 pt-6 font-mono shadow-[9px_9px_0_0_#0a0a0a]"
            transition:scale={{ duration: 200, start: 0.94, opacity: 0, easing: cubicOut }}
        >
            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto overflow-x-hidden pr-1">
                {#each filters as f, i}
                    <div class:pt-3={i > 0} class:border-t={i > 0} class="border-black/20">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 flex-none items-center justify-center border-2 border-black bg-[#ef1653] shadow-[3px_3px_0_0_#0a0a0a]">
                                {#if f.icon === "star"}
                                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]">
                                        <path
                                            d="M12 2.5l2.85 6.16 6.65.63-5.03 4.5 1.5 6.71L12 16.9l-5.97 3.6 1.5-6.71-5.03-4.5 6.65-.63L12 2.5z"
                                            fill="white"
                                        />
                                    </svg>
                                {:else if f.icon === "shield"}
                                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]">
                                        <path
                                            d="M12 2.6l7 2.7v5.4c0 5-3.2 8.6-7 10.7-3.8-2.1-7-5.7-7-10.7V5.3l7-2.7z"
                                            fill="none"
                                            stroke="white"
                                            stroke-width="1.7"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                {:else}
                                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]">
                                        <ellipse cx="12" cy="6" rx="7" ry="3" fill="none" stroke="white" stroke-width="1.6" />
                                        <path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6" fill="none" stroke="white" stroke-width="1.6" />
                                        <path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" fill="none" stroke="white" stroke-width="1.6" />
                                    </svg>
                                {/if}
                            </div>
                            <div>
                                <h3 class="text-base font-bold leading-tight text-black">{f.label}</h3>
                                <p class="mt-0.5 text-[11px] text-neutral-500">{f.rangeLabel}</p>
                            </div>
                        </div>

                        <div class="relative mx-1 mt-4 h-6">
                            <div class={bubbleClass} style="left:{thumbPct(f.low, f)}">
                                {display(f, f.low)}
                            </div>

                            <div class={bubbleClass} style="left:{thumbPct(f.high, f)}">
                                {display(f, f.high)}
                            </div>

                            <div class="absolute inset-x-0 top-1/2 h-[5px] -translate-y-1/2 overflow-hidden rounded-full bg-[#4a4a4a]">
                                <div class="absolute inset-y-0 bg-[#ef1653]" style="left:{pct(f.low, f)}%; right:{100 - pct(f.high, f)}%"></div>
                            </div>

                            <input
                                type="range"
                                class="thumb-range absolute inset-0 w-full appearance-none bg-transparent"
                                min={f.min}
                                max={f.max}
                                step={f.step}
                                bind:value={f.low}
                                oninput={() => onLowInput(f)}
                                aria-label="Minimum {f.label}"
                            />
                            <input
                                type="range"
                                class="thumb-range absolute inset-0 w-full appearance-none bg-transparent"
                                min={f.min}
                                max={f.max}
                                step={f.step}
                                bind:value={f.high}
                                oninput={() => onHighInput(f)}
                                aria-label="Maximum {f.label}"
                            />
                        </div>

                        <div class="flex justify-between px-0.5 text-[11px] text-neutral-500">
                            <span>{display(f, f.min)}</span>
                            <span>{display(f, f.max)}</span>
                        </div>

                        <div class="mt-2 grid grid-cols-2 gap-2.5">
                            <div class="flex items-center gap-2 rounded-[10px] border-2 border-black bg-[#ede9e0] px-2.5 py-[7px]">
                                <span class="text-[11px] text-neutral-500">Min</span>
                                <input type="number" class="max-w-[120px] flex-1 text-sm font-bold text-black" bind:value={f.low} oninput={() => onLowInput(f)} aria-label="Minimum {f.label}" />
                                
                            </div>
                            <div class="flex items-center gap-2 rounded-[10px] border-2 border-black bg-[#ede9e0] px-2.5 py-[7px]">
                                <span class="text-[11px] text-neutral-500">Max</span>
                                <input type="number" class="max-w-[120px] flex-1 text-sm font-bold text-black" bind:value={f.high} oninput={() => onHighInput(f)} aria-label="Maximum {f.label}" />
                                <div class="flex flex-col gap-[2px]">
                                    <button type="button" class={spinBtnClass} aria-label="Increase max {f.label}" onclick={() => nudgeHigh(f, 1)}>
                                        <svg viewBox="0 0 10 6" class="h-[6px] w-[9px]"><path d="M1 5l4-4 4 4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                    <button type="button" class={spinBtnClass} aria-label="Decrease max {f.label}" onclick={() => nudgeHigh(f, -1)}>
                                        <svg viewBox="0 0 10 6" class="h-[6px] w-[9px]"><path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>

            <div class="mt-3 flex shrink-0 gap-3 border-t border-black/20 pt-3">
                <button type="button" class="{btnBase} flex-none basis-[38%] bg-[#ede9e0] text-black" onclick={resetFilters}>Reset</button>
                <button type="button" class="{btnBase} bg-[#ef1653] text-[#f3efe6]" onclick={applyFilters}>Terapkan</button>
            </div>
        </div>
    </div>
{/if}

<style>
    /*
      Tailwind has no way to target the native range-input thumb/track
      pseudo-elements (::-webkit-slider-thumb, ::-moz-range-thumb, etc.),
      so this is the one part of the component that has to stay as real CSS.
    */
    .thumb-range {
        margin: 0;
        pointer-events: none;
    }
    .thumb-range::-webkit-slider-runnable-track {
        background: transparent;
    }
    .thumb-range::-moz-range-track {
        background: transparent;
    }
    .thumb-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        pointer-events: auto;
        width: 22px;
        height: 22px;
        background: #ede9e0;
        border: 2.5px solid #0a0a0a;
        cursor: grab;
        clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
    }
    .thumb-range::-moz-range-thumb {
        pointer-events: auto;
        width: 22px;
        height: 22px;
        background: #ede9e0;
        border: 2.5px solid #0a0a0a;
        cursor: grab;
        clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
    }
</style>