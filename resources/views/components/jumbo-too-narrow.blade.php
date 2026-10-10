@props(['paper'])

{{-- Shown instead of the paper breakdown when the finished roll is wider than the jumbo --}}
<div {{ $attributes->merge(['class' => 'bg-red-50 border border-red-200 text-red-700 text-xs px-3 py-2 rounded']) }}>
    {{ __('bom.jumbo_too_narrow', [
        'jumbo'          => $paper['jumbo'],
        'jumbo_width'    => $paper['jumbo_width_mm'],
        'finished_width' => $paper['finished_width_mm'],
    ]) }}
</div>
