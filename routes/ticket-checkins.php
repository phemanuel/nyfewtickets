<?php

use App\Http\Controllers\Admin\TicketCheckinController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.access'])->prefix('admin/checkins')->name('admin.checkins.')->group(function () {
    Route::get('/', [TicketCheckinController::class, 'index'])->middleware('can:checkins.scan')->name('index');
    Route::post('/lookup', [TicketCheckinController::class, 'lookup'])->middleware(['can:checkins.scan', 'throttle:60,1'])->name('lookup');
    Route::get('/tickets/{ticket}', [TicketCheckinController::class, 'show'])->middleware('can:checkins.scan')->name('show');
    Route::post('/tickets/{ticket}/admit', [TicketCheckinController::class, 'admit'])->middleware('can:checkins.scan')->name('admit');
    Route::post('/tickets/{ticket}/reverse', [TicketCheckinController::class, 'reverse'])->middleware('can:checkins.override')->name('reverse');
});