<script>
    let { message = "Memuat...", compact = false } = $props();
</script>

{#if compact}
    <span class="loading-inline" role="status" aria-live="polite">
        <span class="loading-spinner" aria-hidden="true"></span>
        <span>{message}</span>
    </span>
{:else}
    <div class="loading-block" role="status" aria-live="polite">
        <span class="loading-spinner" aria-hidden="true"></span>
        <span>{message}</span>
    </div>
{/if}

<style>
    .loading-block,
    .loading-inline {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        font-family: "Space Mono", monospace;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .loading-block {
        width: 100%;
        min-height: 4.5rem;
        justify-content: center;
        border: 2px solid #000;
        background: #fffdf5;
        padding: 1.25rem;
    }

    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        flex: 0 0 auto;
        border: 3px solid currentColor;
        border-right-color: transparent;
        border-radius: 999px;
        animation: loading-spin 700ms linear infinite;
    }

    @keyframes loading-spin {
        to { transform: rotate(360deg); }
    }

    @media (prefers-reduced-motion: reduce) {
        .loading-spinner { animation-duration: 1.5s; }
    }
</style>
