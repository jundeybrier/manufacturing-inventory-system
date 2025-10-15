<?php
use App\Http\Controllers\Api\ClientRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/register-user', [ClientRegistrationController::class, 'store']);
