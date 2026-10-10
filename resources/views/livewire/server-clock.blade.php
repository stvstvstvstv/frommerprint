<div wire:poll.1s class="text-sm text-gray-500 font-mono">
    {{ __('dashboard.server_time', ['time' => $now->format('Y-m-d H:i:s')]) }}
</div>
