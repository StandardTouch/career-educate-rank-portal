<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-otp', [App\Http\Controllers\AuthController::class, 'sendRegistrationOtp'])->name('register.send-otp');
    Route::get('/register/verify-mobile', [App\Http\Controllers\AuthController::class, 'showVerifyMobile'])->name('register.verify');
    Route::post('/register/verify-mobile', [App\Http\Controllers\AuthController::class, 'verifyRegistrationOtp'])->name('register.verify.store');
    Route::get('/register/details', [App\Http\Controllers\AuthController::class, 'showRegisterDetails'])->name('register.details');
    Route::post('/register/details', [App\Http\Controllers\AuthController::class, 'completeRegistration'])->name('register.details.store');
});

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'create'])->name('import.excel');
    Route::post('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'store'])->name('import.excel.store');
});

Route::middleware('auth')->group(function () {
Route::get('/results/{dataset:slug}', [App\Http\Controllers\ResultController::class, 'show'])->name('results.show');

// Core routes

// Generated predictor routes
});
