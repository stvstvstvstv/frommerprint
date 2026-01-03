<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\UsersTable;

Route::middleware(['auth'])->group(function () {
    Route::view('/materials', 'materials.index')
        ->name('materials.index');
});

Route::middleware(['auth', 'role:system-admin'])
    ->get('/users', UsersTable::class)
    ->name('users.index');
    
Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
