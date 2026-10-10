<?php

// TODO: prevent deleting or deactivating the current user

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]

class UsersTable extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $locale = 'en';
    public bool $active = true;
    public string $password = '';
    public string $password_confirmation = '';

    public ?int $deleteId = null;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        return view('livewire.users-table', [
            'users' => User::orderBy('name')->paginate(10),
        ])->layout('layouts.app');
    }

    public function add()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->locale = $user->locale;
        $this->active = (bool) ($user->active ?? true);

        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->locale = 'en';
        $this->active = true;
        $this->password = '';
        $this->password_confirmation = '';

        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $this->editingId,
            'locale' => 'required|in:en,ro',
            'active' => 'boolean',
            
            // Password rules
            'password' => $this->editingId
                ? 'nullable|min:8|confirmed'
                : 'required|min:8|confirmed',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        } else {
            unset($data['password']);
        }

        User::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->resetForm();
    }

    // Delete
    public function confirmDelete(): void
    {
        if ($this->isEditingSelf()) 
        {
            abort(403, __('users.cannot_delete_self'));
        }

        $this->deleteId = $this->editingId;
    }

    public function deleteConfirmed(): void
    {
        if ($this->isEditingSelf()) {
            abort(403, __('users.cannot_delete_self'));
        }

        User::findOrFail($this->deleteId)->delete();

        $this->deleteId = null;
        $this->resetForm();
        $this->resetPage();
    }

    public function isEditingSelf(): bool
    {
        return $this->editingId === Auth::id();
    }
}
