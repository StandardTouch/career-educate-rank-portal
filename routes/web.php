<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.store');
    Route::get('/login/verify-mobile', [App\Http\Controllers\AuthController::class, 'showLoginOtp'])->name('login.verify');
    Route::post('/login/verify-mobile', [App\Http\Controllers\AuthController::class, 'verifyLoginOtp'])->name('login.verify.store');
    Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-otp', [App\Http\Controllers\AuthController::class, 'sendRegistrationOtp'])->name('register.send-otp');
    Route::get('/register/verify-mobile', [App\Http\Controllers\AuthController::class, 'showVerifyMobile'])->name('register.verify');
    Route::post('/register/verify-mobile', [App\Http\Controllers\AuthController::class, 'verifyRegistrationOtp'])->name('register.verify.store');
    Route::get('/register/details', [App\Http\Controllers\AuthController::class, 'showRegisterDetails'])->name('register.details');
    Route::post('/register/details', [App\Http\Controllers\AuthController::class, 'completeRegistration'])->name('register.details.store');
});

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])
    ->middleware(['auth', 'single.device'])
    ->name('logout');

// Routes protected by authentication AND having a paid plan
Route::middleware(['auth', 'single.device', 'paid'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/results/{dataset:slug}', [App\Http\Controllers\ResultController::class, 'show'])->name('results.show');
});

// Routes protected by authentication only (allows profile updates and payment)
Route::middleware(['auth', 'single.device'])->group(function () {
    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Payments/Plans
    Route::get('/plans', [App\Http\Controllers\PaymentController::class, 'index'])->name('plans.index');
    Route::get('/plans/checkout/{plan}', [App\Http\Controllers\PaymentController::class, 'checkout'])->name('plans.checkout');
    Route::post('/plans/verify', [App\Http\Controllers\PaymentController::class, 'verify'])->name('plans.verify');

    Route::post('/razorpay/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('razorpay.webhook');
    Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
});

// Admin routes
Route::middleware(['auth', 'single.device', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'create'])->name('import.excel');
    Route::post('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'store'])->name('import.excel.store');
});
