<?php

use App\Http\Controllers\AllIndia2025Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [AllIndia2025Controller::class, 'index'])->name('home');
