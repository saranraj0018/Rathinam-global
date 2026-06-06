<?php

use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\ScholarApplicationController;
use App\Http\Controllers\Web\NewPasswordController;
use App\Http\Controllers\Web\PasswordResetLinkController;
use App\Http\Controllers\Web\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('scholar.create');
});

require __DIR__ . '/admin.php';

// Route::get('/apply', [ScholarApplicationController::class, 'create'])->name('scholar.create');
// Route::post('/apply', [ScholarApplicationController::class, 'store'])->name('scholar.store');
// Route::get('/apply/thank-you', [ScholarApplicationController::class, 'thankyou'])->name('scholar.thankyou');

// Auth UI only — backend team to wire the POST handlers.
Route::view('/login', 'auth.login')->name('auth.login');
Route::post('declaration/confirm', [RegisterController::class, 'confirmDeclaration'])->name('auth.declaration.confirm');
Route::view('/register', 'auth.register')->name('auth.register');
Route::post('/register', [RegisterController::class, 'userRegisterUpdate'])->name('auth.register.store');
Route::post('/login', [RegisterController::class, 'userAuthenticate'])->name('auth.login.store');
Route::post('/logout', [RegisterController::class, 'userLogout'])->name('auth.logout');

//forgot password
Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');

Route::middleware(['auth:user', 'declaration.agreed'])->group(function () {
    Route::get('/apply',                    [ScholarApplicationController::class, 'create'])->name('scholar.create');
    Route::get('/apply/draft',              [ScholarApplicationController::class, 'draft'])->name('scholar.draft');
    Route::post('/apply/step/{step}',       [ScholarApplicationController::class, 'saveStep'])->name('scholar.step');
    Route::post('/apply/initiate-payment',  [PaymentController::class, 'initiatePayment'])->name('scholar.payment.initiate');
    Route::get('/payment/application/{payment_id}', [PaymentController::class, 'paymentApplication'])
    ->name('payment.application');
    Route::get('/payment/success/{payment_id}', [PaymentController::class, 'paymentSuccess'])
    ->name('payment.success');
    Route::post('/apply/submit',            [ScholarApplicationController::class, 'submit'])->name('scholar.submit');
    Route::get('/apply/thankyou',           [ScholarApplicationController::class, 'thankyou'])->name('scholar.thankyou');
});
