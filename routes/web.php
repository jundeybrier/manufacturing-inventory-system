<?php

use App\Http\Controllers\Auth\RegisterUserController;
use App\Livewire\Transactions\All;
use App\Livewire\Transactions\Create;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/register', '/registration');
Route::get('/', function () {
    return view('welcome');
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

Route::middleware(['auth'])->prefix('transactions')->group(function () {
    Route::get('/', All::class)->name('transactions.index');
    Route::get('/create', Create::class)->name('transactions.create');
});

Route::get('/registration', [RegisterUserController::class, 'show'])->name('registration');
Route::post('/registration', [RegisterUserController::class, 'store']);

require __DIR__.'/auth.php';
