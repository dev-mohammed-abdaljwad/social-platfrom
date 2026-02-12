<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

// Public routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Profile routes
Route::get('/profile', function () {
    // For authenticated user's own profile
    $user = auth()->user() ?? User::first(); // Fallback for demo
    return view('profile', [
        'user' => $user,
        'isOwnProfile' => true
    ]);
})->name('profile');

Route::get('/profile/{user}', function (User $user) {
    return view('profile', [
        'user' => $user,
        'isOwnProfile' => auth()->check() && auth()->id() === $user->id
    ]);
})->name('profile.show');

// Other pages
Route::get('/friends', function () {
    return view('friends');
})->name('friends');

Route::get('/settings', function () {
    return view('settings');
})->name('settings');

Route::get('/explore', function () {
    return view('explore');
})->name('explore');