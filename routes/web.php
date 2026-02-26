<?php

use App\Http\Controllers\Api\AreaSuggestController;
use App\Http\Controllers\Api\CareerVacancyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Lk\CareerPageController;
use App\Http\Controllers\Lk\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CareerPageController::class, 'show'])->name('home');

Route::prefix('api')->group(function (): void {
    Route::get('/areas/suggest', [AreaSuggestController::class, 'index']);
    Route::get('/career/vacancies', [CareerVacancyController::class, 'index']);
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');

    Route::get('/register', [RegistrationController::class, 'showEmailStep'])->name('register');
    Route::post('/register/email', [RegistrationController::class, 'sendPin'])->name('register.email');
    Route::get('/register/pin', [RegistrationController::class, 'showPinStep'])->name('register.pin');
    Route::post('/register/pin/verify', [RegistrationController::class, 'verifyPin'])->name('register.pin.verify');
    Route::get('/register/password', [RegistrationController::class, 'showPasswordStep'])->name('register.password');
    Route::post('/register/password', [RegistrationController::class, 'storePassword'])->name('register.password.store');
});

Route::get('/reset-password/{token}', [PasswordResetController::class, 'show'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'store'])->name('password.update');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/lk', [ProfileController::class, 'show'])->name('lk.profile');
    Route::post('/lk/city', [ProfileController::class, 'updateCity'])->name('lk.city.update');
    Route::post('/lk/password', [ProfileController::class, 'updatePassword'])->name('lk.password.update');
    Route::post('/lk/password/reset-link', [ProfileController::class, 'sendPasswordResetLink'])->name('lk.password.reset-link');
});

Route::get('/lk/career', fn () => redirect()->route('home'))->name('lk.career');
