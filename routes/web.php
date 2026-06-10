<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'create'])->name('import.excel');
    Route::post('/import-excel', [App\Http\Controllers\ImportExcelController::class, 'store'])->name('import.excel.store');
});

// Core routes
Route::get('/all-india-2025', [App\Http\Controllers\AllIndia2025Controller::class, 'index'])->name('all-india-2025');
Route::get('/all-india-2022', [App\Http\Controllers\AllIndia2022Controller::class, 'index'])->name('all-india-2022');
Route::get('/all-india-2026', [App\Http\Controllers\AllIndia2026Controller::class, 'index'])->name('all-india-2026');
Route::get('/karnataka-2025', [App\Http\Controllers\Karnataka2025Controller::class, 'index'])->name('karnataka-2025');
Route::get('/karnataka-2024', [App\Http\Controllers\Karnataka2024Controller::class, 'index'])->name('karnataka-2024');
Route::get('/karnataka-2023', [App\Http\Controllers\Karnataka2023Controller::class, 'index'])->name('karnataka-2023');

// Generated predictor routes
Route::get('/all-india-quota-bds-2024-analysis', [App\Http\Controllers\All_india_quota_bds_2024AnalysisController::class, 'index'])->name('all-india-quota-bds-2024-analysis');
Route::get('/all-indida-quota-bds-2025', [App\Http\Controllers\All_indida_quota_bds2025Controller::class, 'index'])->name('all-indida-quota-bds-2025');
Route::get('/all-over-india-data-mbbs-2025', [App\Http\Controllers\All_over_india_data_mbbs2025Controller::class, 'index'])->name('all-over-india-data-mbbs-2025');
Route::get('/all-over-india-mbbs-2024', [App\Http\Controllers\All_over_india_mbbs2024Controller::class, 'index'])->name('all-over-india-mbbs-2024');
Route::get('/andhra-pradesh-bds-govt-quota-2024-analysis', [App\Http\Controllers\Andhra_pradesh_bds_govt_quota_2024AnalysisController::class, 'index'])->name('andhra-pradesh-bds-govt-quota-2024-analysis');
Route::get('/andhra-pradesh-bds-govt-quota-2025-analysis-completed', [App\Http\Controllers\Andhra_pradesh_bds_govt_quota_2025Analysis_completedController::class, 'index'])->name('andhra-pradesh-bds-govt-quota-2025-analysis-completed');
Route::get('/andhra-pradesh-bds-management-quota-2024-analysis', [App\Http\Controllers\Andhra_pradesh_bds_management_quota_2024AnalysisController::class, 'index'])->name('andhra-pradesh-bds-management-quota-2024-analysis');
Route::get('/andhra-pradesh-bds-management-quota-2025-analysis', [App\Http\Controllers\Andhra_pradesh_bds_management_quota_2025AnalysisController::class, 'index'])->name('andhra-pradesh-bds-management-quota-2025-analysis');
Route::get('/andhra-pradesh-mbbs-govt-quota-2024', [App\Http\Controllers\Andhra_pradesh_mbbs_govt_quota2024Controller::class, 'index'])->name('andhra-pradesh-mbbs-govt-quota-2024');
Route::get('/andhra-pradesh-mbbs-management-quota-2024-analysis', [App\Http\Controllers\Andhra_pradesh_mbbs_management_quota_2024AnalysisController::class, 'index'])->name('andhra-pradesh-mbbs-management-quota-2024-analysis');
Route::get('/andra-pradesh-govt-quota-mbbs-2025', [App\Http\Controllers\Andra_pradesh_govt_quota_mbbs2025Controller::class, 'index'])->name('andra-pradesh-govt-quota-mbbs-2025');
Route::get('/andra-pradesh-management-quota-mbbs-2025', [App\Http\Controllers\Andra_pradesh_management_quota_mbbs2025Controller::class, 'index'])->name('andra-pradesh-management-quota-mbbs-2025');
Route::get('/bihar-bds-2025-analysis', [App\Http\Controllers\Bihar_bds_2025AnalysisController::class, 'index'])->name('bihar-bds-2025-analysis');
Route::get('/bihar-mbbs-2025', [App\Http\Controllers\Bihar_mbbs2025Controller::class, 'index'])->name('bihar-mbbs-2025');
Route::get('/chhatisgarh-bds-2025', [App\Http\Controllers\Chhatisgarh_bds2025Controller::class, 'index'])->name('chhatisgarh-bds-2025');
Route::get('/chhattisgarh-mbbs-2025', [App\Http\Controllers\Chhattisgarh_mbbs2025Controller::class, 'index'])->name('chhattisgarh-mbbs-2025');
Route::get('/deemed-universities-quota-bds-2024-analysis', [App\Http\Controllers\Deemed_universities_quota_bds_2024AnalysisController::class, 'index'])->name('deemed-universities-quota-bds-2024-analysis');
Route::get('/deemed-universities-quota-bds-2025', [App\Http\Controllers\Deemed_universities_quota_bds2025Controller::class, 'index'])->name('deemed-universities-quota-bds-2025');
Route::get('/deemed-universities-quota-mbbs-2024', [App\Http\Controllers\Deemed_universities_quota_mbbs2024Controller::class, 'index'])->name('deemed-universities-quota-mbbs-2024');
Route::get('/deemed-university-mbbs-2025', [App\Http\Controllers\Deemed_university_mbbs2025Controller::class, 'index'])->name('deemed-university-mbbs-2025');
Route::get('/haryana-bds-2025', [App\Http\Controllers\Haryana_bds2025Controller::class, 'index'])->name('haryana-bds-2025');
Route::get('/haryana-mbbs-2025', [App\Http\Controllers\Haryana_mbbs2025Controller::class, 'index'])->name('haryana-mbbs-2025');
Route::get('/jharkhand-mbbs-management-quota-2024-analysis', [App\Http\Controllers\Jharkhand_mbbs_management_quota_2024AnalysisController::class, 'index'])->name('jharkhand-mbbs-management-quota-2024-analysis');
Route::get('/karnataka-bds-2024', [App\Http\Controllers\Karnataka_bds2024Controller::class, 'index'])->name('karnataka-bds-2024');
Route::get('/karnataka-bds-2025', [App\Http\Controllers\Karnataka_bds2025Controller::class, 'index'])->name('karnataka-bds-2025');
Route::get('/karnataka-mbbs-2025-analysis', [App\Http\Controllers\Karnataka_mbbs_2025AnalysisController::class, 'index'])->name('karnataka-mbbs-2025-analysis');
Route::get('/keral-mbbs-2025', [App\Http\Controllers\Keral_mbbs2025Controller::class, 'index'])->name('keral-mbbs-2025');
Route::get('/kerala-bds-2024-analysis', [App\Http\Controllers\Kerala_bds_2024AnalysisController::class, 'index'])->name('kerala-bds-2024-analysis');
Route::get('/kerala-bds-2025', [App\Http\Controllers\Kerala_bds2025Controller::class, 'index'])->name('kerala-bds-2025');
Route::get('/madhya-pradesh-bds-2025', [App\Http\Controllers\Madhya_pradesh_bds2025Controller::class, 'index'])->name('madhya-pradesh-bds-2025');
Route::get('/madhya-pradesh-mbbs-2025', [App\Http\Controllers\Madhya_pradesh_mbbs2025Controller::class, 'index'])->name('madhya-pradesh-mbbs-2025');
Route::get('/my-rank-mbbs-karnataka-2024', [App\Http\Controllers\My_rank_mbbs_karnataka2024Controller::class, 'index'])->name('my-rank-mbbs-karnataka-2024');
Route::get('/my-rank-mbbs-keral-2024', [App\Http\Controllers\My_rank_mbbs_keral2024Controller::class, 'index'])->name('my-rank-mbbs-keral-2024');
Route::get('/my-rank-mbbs-rajasthan-2024', [App\Http\Controllers\My_rank_mbbs_rajasthan2024Controller::class, 'index'])->name('my-rank-mbbs-rajasthan-2024');
Route::get('/my-rank-uttar-pradesh-dental-2024', [App\Http\Controllers\My_rank_uttar_pradesh_dental2024Controller::class, 'index'])->name('my-rank-uttar-pradesh-dental-2024');
Route::get('/puducherry-bds-2025', [App\Http\Controllers\Puducherry_bds2025Controller::class, 'index'])->name('puducherry-bds-2025');
Route::get('/puducherry-mbbs-2025', [App\Http\Controllers\Puducherry_mbbs2025Controller::class, 'index'])->name('puducherry-mbbs-2025');
Route::get('/rajasthan-bds-2025-analysis', [App\Http\Controllers\Rajasthan_bds_2025AnalysisController::class, 'index'])->name('rajasthan-bds-2025-analysis');
Route::get('/rajasthan-bds-2024', [App\Http\Controllers\Rajasthan_bds2024Controller::class, 'index'])->name('rajasthan-bds-2024');
Route::get('/rajasthan-mbbs-2025', [App\Http\Controllers\Rajasthan_mbbs2025Controller::class, 'index'])->name('rajasthan-mbbs-2025');
Route::get('/tamil-nadu-bds-government-quota-2024-analysis', [App\Http\Controllers\Tamil_nadu_bds_government_quota_2024AnalysisController::class, 'index'])->name('tamil-nadu-bds-government-quota-2024-analysis');
Route::get('/tamil-nadu-bds-government-quota-2025-analysis', [App\Http\Controllers\Tamil_nadu_bds_government_quota_2025AnalysisController::class, 'index'])->name('tamil-nadu-bds-government-quota-2025-analysis');
Route::get('/tamil-nadu-bds-management-quota-2024-analysis', [App\Http\Controllers\Tamil_nadu_bds_management_quota_2024AnalysisController::class, 'index'])->name('tamil-nadu-bds-management-quota-2024-analysis');
Route::get('/tamil-nadu-bds-management-quota-2025-analysis', [App\Http\Controllers\Tamil_nadu_bds_management_quota_2025AnalysisController::class, 'index'])->name('tamil-nadu-bds-management-quota-2025-analysis');
Route::get('/tamil-nadu-mbbs-government-quota-2024', [App\Http\Controllers\Tamil_nadu_mbbs_government_quota2024Controller::class, 'index'])->name('tamil-nadu-mbbs-government-quota-2024');
Route::get('/tamil-nadu-mbbs-management-quota-2024-analysis', [App\Http\Controllers\Tamil_nadu_mbbs_management_quota_2024AnalysisController::class, 'index'])->name('tamil-nadu-mbbs-management-quota-2024-analysis');
Route::get('/tamilnadu-govt-quota-mbbs-2025', [App\Http\Controllers\Tamilnadu_govt_quota_mbbs2025Controller::class, 'index'])->name('tamilnadu-govt-quota-mbbs-2025');
Route::get('/tamilnadu-management-quota-mbbs-2025', [App\Http\Controllers\Tamilnadu_management_quota_mbbs2025Controller::class, 'index'])->name('tamilnadu-management-quota-mbbs-2025');
Route::get('/telanaga-govt-mbbs-2025', [App\Http\Controllers\Telanaga_govt_mbbs2025Controller::class, 'index'])->name('telanaga-govt-mbbs-2025');
Route::get('/telangana-bds-govt-quota-2024-analysis', [App\Http\Controllers\Telangana_bds_govt_quota_2024AnalysisController::class, 'index'])->name('telangana-bds-govt-quota-2024-analysis');
Route::get('/telangana-bds-govt-quota-2025-analysis', [App\Http\Controllers\Telangana_bds_govt_quota_2025AnalysisController::class, 'index'])->name('telangana-bds-govt-quota-2025-analysis');
Route::get('/telangana-bds-management-quota-2024-analysis', [App\Http\Controllers\Telangana_bds_management_quota_2024AnalysisController::class, 'index'])->name('telangana-bds-management-quota-2024-analysis');
Route::get('/telangana-bds-management-quota-2025-analysis', [App\Http\Controllers\Telangana_bds_management_quota_2025AnalysisController::class, 'index'])->name('telangana-bds-management-quota-2025-analysis');
Route::get('/telangana-management-quota-mbbs-2025', [App\Http\Controllers\Telangana_management_quota_mbbs2025Controller::class, 'index'])->name('telangana-management-quota-mbbs-2025');
Route::get('/telangana-2024-mbbs-govt-data', [App\Http\Controllers\Telangana2024Mbbs_govt_dataController::class, 'index'])->name('telangana-2024-mbbs-govt-data');
Route::get('/telangana-2024-mbbs-mang-data', [App\Http\Controllers\Telangana2024Mbbs_mang_dataController::class, 'index'])->name('telangana-2024-mbbs-mang-data');
Route::get('/up-mbbs-2024', [App\Http\Controllers\Up_mbbs2024Controller::class, 'index'])->name('up-mbbs-2024');
Route::get('/uttar-pradesh-bds-2025', [App\Http\Controllers\Uttar_pradesh_bds2025Controller::class, 'index'])->name('uttar-pradesh-bds-2025');
Route::get('/uttar-pradesh-mbbs-2025', [App\Http\Controllers\Uttar_pradesh_mbbs2025Controller::class, 'index'])->name('uttar-pradesh-mbbs-2025');
Route::get('/uttarakhand-bds-2025', [App\Http\Controllers\Uttarakhand_bds2025Controller::class, 'index'])->name('uttarakhand-bds-2025');
Route::get('/uttarakhand-mbbs-2025', [App\Http\Controllers\Uttarakhand_mbbs2025Controller::class, 'index'])->name('uttarakhand-mbbs-2025');
Route::get('/west-bengal-bds-2025', [App\Http\Controllers\West_bengal_bds2025Controller::class, 'index'])->name('west-bengal-bds-2025');
Route::get('/west-bengal-mbbs-2025', [App\Http\Controllers\West_bengal_mbbs2025Controller::class, 'index'])->name('west-bengal-mbbs-2025');
Route::get('/all-india-quota-bds-2023-analysis', [App\Http\Controllers\All_india_quota_bds2023AnalysisController::class, 'index'])->name('all-india-quota-bds-2023-analysis');
