@props(['problems' => []])

{{-- Materials the cost calculator could not price (left out of the total) --}}
@if (count($problems) > 0)
    <div {{ $attributes->merge(['class' => 'bg-red-50 border border-red-200 text-red-700 text-xs px-3 py-2 rounded space-y-1']) }}>
        <div class="font-semibold">{{ __('bom.problems.heading') }}</div>
        <ul class="list-disc ps-4">
            @foreach ($problems as $problem)
                <li>{{ __('bom.problems.' . $problem['code'], $problem) }}</li>
            @endforeach
        </ul>
        <div>{{ __('bom.problems.fix') }}</div>
    </div>
@endif
