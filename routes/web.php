<?php

use App\Http\Controllers\ScholarApplicationController;
use App\Http\Controllers\Web\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('scholar.create');
});

require __DIR__ . '/admin.php';

Route::get('/apply', [ScholarApplicationController::class, 'create'])->name('scholar.create');
Route::post('/apply', [ScholarApplicationController::class, 'store'])->name('scholar.store');
Route::get('/apply/thank-you', [ScholarApplicationController::class, 'thankyou'])->name('scholar.thankyou');

// Auth UI only — backend team to wire the POST handlers.
Route::view('/login', 'auth.login')->name('auth.login');
Route::view('/register', 'auth.register')->name('auth.register');
Route::post('/register', [RegisterController::class, 'userRegisterUpdate'])->name('auth.register.store');
Route::post('/login', [RegisterController::class, 'userAuthenticate'])->name('auth.login.store');
Route::post('/logout', [RegisterController::class, 'userLogout'])->name('auth.logout');
