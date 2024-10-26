<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordController;

// Home page
Route::get('/', function () {
    return view('home');
})->name('home');

// About page
Route::get('/about', function () {
    return view('about');
})->name('about');

// Contact page
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Pricing page
Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

// Products page
Route::get('/products', function () {
    return view('products');
})->name('products');

// Register page and routes
Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [UserController::class, 'store'])->name('register.store');
Route::get('/register/success', function () {
    return view('registered');
})->name('register.success');

// Sign In routes
Route::get('/signin', function () {
    return view('signin');
})->name('signin');
Route::post('/signin', [AuthController::class, 'authenticate'])->name('signin.store');

// Dashboard route
Route::get('/dashboard/home', [DashboardController::class, 'showDashboard'])->middleware('auth')->name('dashboard.home');

// Password reset routes (Assuming you have this set up)
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');
