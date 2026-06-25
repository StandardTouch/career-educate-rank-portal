<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [App\Http\Controllers\AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [App\Http\Controllers\AuthController::class, 'adminLogin'])->name('admin.login.store');
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

Route::match(['get', 'post'], '/exotel/voice-analyze-webhook', [App\Http\Controllers\AdminDashboardController::class, 'exotelVoiceAnalyzeWebhook'])
    ->name('exotel.voice-analyze-webhook');

// Routes protected by authentication AND having a paid plan
Route::middleware(['auth', 'single.device', 'paid'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $datasets = \App\Models\Dataset::query()
            ->where('is_active', true)
            ->withCount('rankRecords')
            ->orderByDesc('year')
            ->orderBy('course')
            ->orderBy('title')
            ->take(8)
            ->get();

        $latestMbbs = $datasets->first(fn ($dataset) => strtoupper((string) $dataset->course) === 'MBBS');
        $latestBds = $datasets->first(fn ($dataset) => strtoupper((string) $dataset->course) === 'BDS');
        $datasetCount = \App\Models\Dataset::where('is_active', true)->count();
        $recordCount = \App\Models\RankRecord::count();
        $availableYears = \App\Models\Dataset::where('is_active', true)->whereNotNull('year')->distinct()->count('year');

        $profileFields = collect([
            $user->name,
            $user->email,
            $user->phone,
            $user->neet_rank,
            $user->neet_marks,
            $user->state,
            $user->quota,
        ]);
        $profileCompletion = (int) round(($profileFields->filter(fn ($value) => filled($value))->count() / $profileFields->count()) * 100);

        return view('dashboard', compact(
            'user',
            'datasets',
            'latestMbbs',
            'latestBds',
            'datasetCount',
            'recordCount',
            'availableYears',
            'profileCompletion'
        ));
    })->name('dashboard');

    Route::get('/neet-ug-2025-analysis', [App\Http\Controllers\NeetAnalysisController::class, 'show'])->name('neet.analysis');
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
    Route::get('/admin/users', [App\Http\Controllers\AdminDashboardController::class, 'users'])->name('admin.users');
    Route::get('/admin/payments', [App\Http\Controllers\AdminDashboardController::class, 'payments'])->name('admin.payments');
    Route::get('/admin/call-details', [App\Http\Controllers\AdminDashboardController::class, 'callDetails'])->name('admin.call-details');
    Route::get('/admin/call-recording', [App\Http\Controllers\AdminDashboardController::class, 'callRecording'])->name('admin.call-recording');
    Route::post('/admin/call-transcript', [App\Http\Controllers\AdminDashboardController::class, 'callTranscript'])->name('admin.call-transcript');
    Route::get('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'create'])->name('import.excel');
    Route::post('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'store'])->name('import.excel.store');
    Route::get('/admin/import-analysis', [App\Http\Controllers\ImportAnalysisController::class, 'create'])->name('import.analysis');
    Route::post('/admin/import-analysis', [App\Http\Controllers\ImportAnalysisController::class, 'store'])->name('import.analysis.store');
});

// Analysis Routes (OTP based or regular Auth)
Route::get('/analysis/login', [App\Http\Controllers\AnalysisAuthController::class, 'showPhoneForm'])->name('analysis.login');
Route::post('/analysis/send-otp', [App\Http\Controllers\AnalysisAuthController::class, 'sendOtp'])->name('analysis.send-otp');
Route::get('/analysis/verify', [App\Http\Controllers\AnalysisAuthController::class, 'showVerifyForm'])->name('analysis.verify');
Route::post('/analysis/verify', [App\Http\Controllers\AnalysisAuthController::class, 'verifyOtp'])->name('analysis.verify.store');

Route::middleware([\App\Http\Middleware\AnalysisAccess::class])->group(function () {
    Route::get('/analysis/{analysis_dataset:slug}', [App\Http\Controllers\AnalysisResultController::class, 'show'])->name('analysis.show');
});
