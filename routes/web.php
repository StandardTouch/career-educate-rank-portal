<?php

use App\Http\Controllers\AllIndia2025Controller;
use App\Http\Controllers\Karnataka2025Controller;
use App\Http\Controllers\Karnataka2024Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [AllIndia2025Controller::class, 'index'])->name('home');
Route::get('/karnataka-2025', [Karnataka2025Controller::class, 'index'])->name('karnataka-2025');
Route::get('/karnataka-2024', [Karnataka2024Controller::class, 'index'])->name('karnataka-2024');