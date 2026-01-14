<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Public (guest) routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->middleware('guest');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // === YOUR APP ROUTES (RESTORED) ===

    Route::get('/materials', function () {
        return view('materials.index');
    })->name('materials.index');

    Route::view('/admin/currencies', 'admin.currencies')
        ->name('admin.currencies');

    Route::view('/admin/exchange-rates', 'admin.exchange-rates')
        ->name('admin.exchange-rates');

    Route::view('/users', 'users.index')
        ->name('users.index');

    // Breeze profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
