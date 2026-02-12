<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/explore', [PageController::class, 'explore'])->name('explore');

// Profile routes
Route::get('/profile', [PageController::class, 'profile'])->name('profile');
Route::get('/profile/{user}', [PageController::class, 'showProfile'])->name('profile.show');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    
    // Pages
    Route::get('/friends', [PageController::class, 'friends'])->name('friends');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings');
});