<x-guest-layout>
    <div class="text-center">
       <!-- <img src="{{ asset('images/frommerprint_lung_354.jpg') }}"
             class="mx-auto mb-6 w-32"
             alt="Frommerprint"> --!>

        <h1 class="text-2xl font-semibold mb-4">
            {{ __('common.welcome') }}
        </h1>

        <a href="{{ route('login') }}" class="text-blue-600 underline">
            Login
        </a>
    </div>
</x-guest-layout>