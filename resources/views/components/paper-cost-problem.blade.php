@props(['paper'])

{{-- Shown instead of the paper breakdown when paper cost can't be calculated --}}
<div {{ $attributes->merge(['class' => 'bg-red-50 border border-red-200 text-red-700 text-xs px-3 py-2 rounded']) }}>
    @if ($paper['error'] === 'missing_dimensions')
        {{ __('bom.missing_dimensions', [
            'fields' => collect($paper['missing'])->map(fn ($field) => __('bom.dimensions.' . $field))->join(', '),
        ]) }}
    @else
        {{ __('bom.jumbo_too_narrow', [
            'jumbo'          => $paper['jumbo'],
            'jumbo_width'    => $paper['jumbo_width_mm'],
            'finished_width' => $paper['finished_width_mm'],
        ]) }}
    @endif
</div>
