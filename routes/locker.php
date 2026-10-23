<?php

use App\Http\Controllers\LockerAdminController;
use App\Http\Middleware\LockerAdminSession;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [LockerAdminController::class, 'loginPage'])->name('locker.login');
Route::post('/admin/login', [LockerAdminController::class, 'login'])->middleware('throttle:5,1')->name('locker.login.submit');
Route::post('/admin/logout', [LockerAdminController::class, 'logout'])->middleware(LockerAdminSession::class)->name('locker.logout');

Route::middleware(LockerAdminSession::class)->group(function () {
    Route::get('/admin', [LockerAdminController::class, 'dashboard'])->name('locker.dashboard');
    Route::get('/admin/data', [LockerAdminController::class, 'data'])->name('locker.data');

    Route::post('/admin/students', [LockerAdminController::class, 'storeStudent'])->name('locker.students.store');
    Route::put('/admin/students/{id}', [LockerAdminController::class, 'updateStudent'])
        ->where('id', '[A-Za-z0-9_-]+')->name('locker.students.update');

    Route::post('/admin/cards', [LockerAdminController::class, 'storeCard'])->name('locker.cards.store');
    Route::patch('/admin/cards/{uid}', [LockerAdminController::class, 'updateCardStatus'])
        ->where('uid', '[A-Fa-f0-9]+')->name('locker.cards.status');

    Route::post('/admin/lockers', [LockerAdminController::class, 'storeLocker'])->name('locker.lockers.store');
    Route::put('/admin/lockers/{id}', [LockerAdminController::class, 'updateLocker'])
        ->where('id', '[A-Za-z0-9_-]+')->name('locker.lockers.update');
});
