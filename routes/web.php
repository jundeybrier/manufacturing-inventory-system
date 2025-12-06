<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\StageMovementController;
use App\Livewire\Inventory\ManageInventoryItems;

Route::redirect('/register', '/registration');
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

});

Route::prefix('stage-movements')->group(function () {
    Route::get('/', [StageMovementController::class, 'index'])->name('stage-movements.index');
    Route::get('/create', [StageMovementController::class, 'create'])->name('stage-movements.create');
    Route::post('/', [StageMovementController::class, 'store'])->name('stage-movements.store');
});

Route::get('/inventory-items', ManageInventoryItems::class)
    ->name('inventory-items.index');

Route::middleware(['auth'])->group(function () {

    // Production Wizard
    Route::get('/production/create', \App\Livewire\Production\ProductionWizard::class)
        ->name('production.create');

    // Production Batch Index
    Route::get('/production', [\App\Http\Controllers\Production\ProductionBatchController::class, 'index'])
        ->name('production.index');

//    // Production Batch Details
//    Route::get('/production/{batch}', [\App\Http\Controllers\Production\ProductionBatchController::class, 'show'])
//        ->name('production.show');

    Route::get('/production/{batchId}', \App\Livewire\Production\ProductionShow::class)
        ->name('production.show');

    Route::get('/bom', \App\Livewire\Bom\BomIndex::class)->name('bom.index');

    Route::get('/bom/{product}', \App\Livewire\Bom\BomEditor::class)
        ->name('bom.edit');

    Route::get('/stages', \App\Livewire\Stages\StageManager::class)->name('stages.index');

});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
});


require __DIR__.'/auth.php';
