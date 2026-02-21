<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
            <livewire:server-clock />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                    <a
                        href="{{ url('/materials') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-sm font-medium rounded hover:bg-indigo-700"
                    >
                        Materials
                    </a>

                    <a
                        href="{{ url('/admin/currencies') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-sm font-medium rounded hover:bg-indigo-700"
                    >
                        Currencies
                    </a>

                    <a
                        href="{{ url('/bom/17') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-sm font-medium rounded hover:bg-indigo-700"
                    >
                        BOM #17
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
