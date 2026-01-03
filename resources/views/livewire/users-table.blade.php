<div class="py-6 max-w-7xl mx-auto px-6">
    <h1 class="text-xl font-semibold mb-4">
        {{ __('users.title') }}
    </h1>

<div class="flex justify-end mb-4">
    <button
        wire:click="add"
        class="text-blue-600 hover:underline"
    >
        {{ __('users.add') }}
    </button>
</div>

        {{-- EDIT PANEL (conditionally visible) --}}
@if ($showForm)
    <div class="bg-white shadow rounded p-4 mb-4">
        <h3 class="font-semibold mb-4">
            {{ $editingId ? __('users.edit') : __('users.add') }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input
                type="text"
                wire:model.defer="name"
                placeholder="{{ __('users.fields.name') }}"
                class="rounded border-gray-300"
            >

            <input
                type="email"
                wire:model.defer="email"
                placeholder="{{ __('users.fields.email') }}"
                class="rounded border-gray-300"
            >

            <select wire:model.defer="locale" class="rounded border-gray-300">
                <option value="en">English</option>
                <option value="ro">Română</option>
            </select>

            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="active">
                {{ __('common.active') }}
            </label>
        </div>

        <input
            type="password"
            wire:model.defer="password"
            placeholder="{{ __('users.fields.password') }}"
            class="rounded border-gray-300"
        />

        <input
            type="password"
            wire:model.defer="password_confirmation"
            placeholder="{{ __('users.fields.password_confirmation') }}"
            class="rounded border-gray-300"
        />

        <div class="mt-4 flex gap-2">
            <button
                wire:click="save"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                {{ __('common.save') }}
            </button>

            <button
                wire:click="resetForm"
                class="px-4 py-2 rounded border border-gray-300"
            >
                {{ __('common.cancel') }}
            </button>
        </div>
    </div>
@endif
    
    <div class="bg-white shadow rounded">
        <table class="min-w-full text-sm">
            <thead class="border-b">
                <tr>
                    <th class="p-2 text-left">{{ __('users.fields.name') }}</th>
                    <th class="p-2 text-left">{{ __('users.fields.email') }}</th>
                    <th class="p-2 text-left">{{ __('users.fields.locale') }}</th>
                    <th class="p-2 text-center">{{ __('common.active') }}</th>
                    <th class="p-2">{{ __('common.actions') }}</th>

                </tr>
            </thead>
            <tbody class="divide-y">
            @foreach ($users as $user)
                <tr>
                    <td class="p-2">{{ $user->name }}</td>
                    <td class="p-2">{{ $user->email }}</td>
                    <td class="p-2 uppercase">{{ $user->locale }}</td>
                    <td class="p-2 text-center">
                        {{ $user->active ? '✓' : '—' }}
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-2 text-right">
                        <div class="inline-flex gap-1">
                            {{-- EDIT --}}
                            <button
                                wire:click="edit({{ $user->id }})"
                                class="text-gray-500 hover:text-gray-800"
                            >
                                {{ __('common.edit') }}
                            </button>

                            {{-- DELETE --}}
                            <button
                                wire:click="confirmDelete({{ $user->id }})"
                                class="text-gray-500 hover:text-red-700"
                            >
                                {{ __('common.delete') }}
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>