<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController; // Import AuthController for handling sign-in
use App\Http\Controllers\PasswordController; // Import for handling password reset

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

// Register page - Show form
Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');

// Handle form submission and store in the database
Route::post('/register', [UserController::class, 'store'])->name('register.store');

// Registered confirmation page (show registered.blade.php view after registration)
Route::get('/register/success', function () {
    return view('registered'); // Show the registered.blade.php view
})->name('register.success');

// Thanks page
Route::get('/thanks', function () {
    return view('thanks');
})->name('thanks');

// Sign In page
Route::get('/signin', function () {
    return view('signin');
})->name('signin');

// Handle sign-in form submission
Route::post('/signin', [AuthController::class, 'authenticate'])->name('signin.store');

// Password reset routes
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');
