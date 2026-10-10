@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

    <div>
        <h1 class="text-xl font-semibold">
            BOM for {{ $bom->finishedMaterial->name ?? '—' }}
        </h1>

        <div class="text-sm text-gray-500">
            Setup time: {{ $bom->setup_time_minutes }} minutes
        </div>
    </div>

    {{-- BOM Items --}}
    <livewire:boms.bom-items-table :bom="$bom" />

</div>
@endsection