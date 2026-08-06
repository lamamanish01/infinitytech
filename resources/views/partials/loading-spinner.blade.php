<div class="d-flex flex-column justify-content-center align-items-center py-3" style="min-height: 100px;">
    {{-- Small spinning icon --}}
    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>

    {{-- Small "Loading..." text with dots --}}
    <p class="text-muted fw-light mb-0" style="font-size: 0.9rem;">
        Loading<span class="dot-loader">...</span>
    </p>
</div>

<style>
    /* Animated dots */
    .dot-loader {
        display: inline-block;
        width: 1.2em;
        text-align: left;
        animation: dots 1.5s steps(4, end) infinite;
    }
    @keyframes dots {
        0%   { opacity: 0; }
        25%  { opacity: 0.2; }
        50%  { opacity: 0.5; }
        75%  { opacity: 0.8; }
        100% { opacity: 1; }
    }
</style>
