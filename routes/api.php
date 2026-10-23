<?php

use App\Http\Controllers\LockerApiController;
use Illuminate\Support\Facades\Route;

Route::post('/locker/verify', [LockerApiController::class, 'verify'])->middleware('throttle:30,1');
