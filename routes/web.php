<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Livewire\BomEditor;
use App\Http\Controllers\BomController;

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

    Route::get('/production-orders',              \App\Livewire\ProductionOrdersIndex::class)->name('production-orders.index');
    Route::get('/production-orders/create',       \App\Livewire\ProductionOrderCreate::class)->name('production-orders.create');
    Route::get('/production-orders/{order}/edit', \App\Livewire\ProductionOrderEdit::class)->name('production-orders.edit');

    Route::get('/bom/{material}', BomEditor::class)->name('bom.editor');

    Route::get('/bom/{bom}/items', [BomController::class, 'items'])
        ->name('bom.items');

    Route::get('/dashboard', [\App\Http\Controllers\HomeController::class, 'index'])
        ->name('dashboard');

    // === YOUR APP ROUTES (RESTORED) ===

    Route::get('/materials', function () {
        return view('materials.index');
    })->name('materials.index');

    Route::view('/admin/currencies', 'admin.currencies')
        ->name('admin.currencies');

    Route::view('/admin/exchange-rates', 'admin.exchange-rates')
        ->name('admin.exchange-rates');

    Route::get('/users', \App\Livewire\UsersTable::class)
        ->name('users.index');

    // Breeze profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
