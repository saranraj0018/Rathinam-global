<?php

use App\Http\Controllers\API\ApplicationController;
use Illuminate\Support\Facades\Route;



Route::get('/application-lists',[ApplicationController::class, 'index']);
