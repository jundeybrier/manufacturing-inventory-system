<?php

use App\Http\Controllers\Auth\RegisterUserController;
use App\Livewire\Transactions\All;
use App\Livewire\Transactions\Create;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/register', '/registration');
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified','site_permission'])
    ->name('dashboard');


Route::middleware(['auth','site_permission'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

});

Route::middleware(['auth','site_permission'])->prefix('transactions')->group(function () {
    Route::get('/', All::class)->name('transactions.index');
    Route::get('/create', Create::class)->name('transactions.create');
    Route::get('/transactions/daily-report', [\App\Http\Controllers\TransactionReportController::class, 'daily'])
        ->name('transactions.daily-report');
});

Route::get('/print/receipt/{transaction}', [\App\Http\Controllers\ReceiptController::class, 'show'])
    ->name('print.receipt');

Route::middleware(['auth', 'role:admin','site_permission'])->group(function () {
    Route::get('/products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/services', \App\Livewire\Services\Index::class)->name('services.index');
    Route::get('/accounts', \App\Livewire\Accounts\Index::class)->name('accounts.index');
    Route::get('/fee-components', \App\Livewire\FeeComponents\Index::class)->name('fee-components.index');
    Route::get('/offices', \App\Livewire\Offices\Index::class)->name('offices.index');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
});

Route::middleware(['site_permission'])->group(function () {
    Route::get('/registration', [RegisterUserController::class, 'show'])->name('registration');
    Route::post('/registration', [RegisterUserController::class, 'store'])->name('registration.store');
    Route::get('/client-register', [RegisterUserController::class, 'show'])->name('client.register');
    Route::post('/client-register', [RegisterUserController::class, 'store'])->name('client.register.store');
});

if (config('app.mode') === 'client') {
    Route::get('/sync-status', \App\Livewire\SyncStatus::class)->name('sync.status');
}

require __DIR__.'/auth.php';
