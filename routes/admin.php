<?php

use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::middleware(['guest'])->as('admin.')->group(function () {
        Route::view('/login', 'admin.login.index')->name('login');
        Route::controller(AuthController::class)->group(function () {
            Route::post('/authenticate', 'adminAuthenticate')->name('authenticate');
            Route::post('/register/update', 'registerUpdate')->name('register.update');
        });
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/user_logout', [AuthController::class, 'user_logout'])->name('user_logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // routes/api.php
        Route::get('/applications', [ApplicationController::class, 'index'])->name('applications');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications_show');
        Route::get('/applications/{application}/documents/{document}/download', [ApplicationController::class, 'download'])->name('download');
        Route::get('/applications/{application}/documents/download-all', [ApplicationController::class, 'downloadAll'])->name('download_all');

    });
});
