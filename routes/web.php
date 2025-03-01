<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/register-form', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::get('/login-form', [AuthController::class, 'showLoginForm'])->name('login');
Route::view('/verify-otp', 'auth.verify-otp')->name('verify.otp');
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
Route::get('/customer-form',[CustomerController::class,'viewForm']);
