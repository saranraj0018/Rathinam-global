<?php

use App\Http\Controllers\ScholarApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('scholar.create');
});


Route::get('/apply', [ScholarApplicationController::class, 'create'])->name('scholar.create');
Route::post('/apply', [ScholarApplicationController::class, 'store'])->name('scholar.store');

require __DIR__ . '/admin.php';
