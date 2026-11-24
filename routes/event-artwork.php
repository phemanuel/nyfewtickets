<?php

use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.access', 'can:events.create'])
    ->prefix('admin/events')
    ->name('admin.events.')
    ->group(function () {
        Route::get('{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::patch('{event}', [EventController::class, 'update'])->name('update');
    });
