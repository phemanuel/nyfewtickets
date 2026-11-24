<?php

use App\Http\Controllers\Admin\GateScannerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.access', 'can:checkins.scan'])
    ->prefix('admin/scan')->name('admin.gate.')->group(function () {
        Route::get('/', [GateScannerController::class, 'index'])->name('index');
        Route::post('/lookup', [GateScannerController::class, 'lookup'])->middleware('throttle:90,1')->name('lookup');
        Route::get('/tickets/{ticket}', [GateScannerController::class, 'show'])->name('show');
        Route::get('/tickets/{ticket}/photo', [GateScannerController::class, 'photo'])->name('photo');
        Route::post('/tickets/{ticket}/enter', [GateScannerController::class, 'enter'])->name('enter');
        Route::post('/tickets/{ticket}/exit', [GateScannerController::class, 'exit'])->middleware('can:checkins.exit')->name('exit');
        Route::post('/tickets/{ticket}/correct-exit', [GateScannerController::class, 'correctExit'])->middleware('can:checkins.override')->name('correct-exit');
    });
