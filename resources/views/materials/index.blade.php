<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('materials.title') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-4 shadow rounded">
            {{ __('materials.title') }}
        </div>

        <div class="bg-white p-4 shadow rounded">
            <livewire:materials-table />
        </div>
    </div>
</x-app-layout>