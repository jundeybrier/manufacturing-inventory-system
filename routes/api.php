<?php
use App\Http\Controllers\Api\ClientRegistrationController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/register-user', [ClientRegistrationController::class, 'store']);
Route::post('/sync', [SyncController::class, 'sync']);
Route::post('/sync/transactions', [\App\Http\Controllers\Api\SyncTransactionsController::class, 'store']);
