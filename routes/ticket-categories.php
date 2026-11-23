<?php

use App\Http\Controllers\Admin\TicketCategoryController;
use Illuminate\Support\Facades\Route;

// Include once at the bottom of routes/web.php (outside any other route group).
Route::prefix('admin/events/{event}/categories')
    ->name('admin.events.categories.')
    ->middleware(['auth', 'can:admin.access'])
    ->group(function () {
        Route::get('/', [TicketCategoryController::class, 'index'])->middleware('can:events.view')->name('index');
        Route::get('/create', [TicketCategoryController::class, 'create'])->middleware('can:events.create')->name('create');
        Route::post('/', [TicketCategoryController::class, 'store'])->middleware('can:events.create')->name('store');
        Route::get('/{category}/edit', [TicketCategoryController::class, 'edit'])->middleware('can:events.edit')->name('edit');
        Route::put('/{category}', [TicketCategoryController::class, 'update'])->middleware('can:events.edit')->name('update');
    });
