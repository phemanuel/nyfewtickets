<?php

use App\Http\Controllers\Admin\AccessRoleController;
use App\Http\Controllers\Admin\AccessUserController;
use Illuminate\Support\Facades\Route;

// Included once from routes/web.php, outside its existing route groups.
Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'can:admin.access'])->group(function () {
    Route::get('users', [AccessUserController::class, 'index'])->middleware('can:staff.view')->name('users.index');
    Route::get('users/create', [AccessUserController::class, 'create'])->middleware('can:staff.manage')->name('users.create');
    Route::post('users', [AccessUserController::class, 'store'])->middleware('can:staff.manage')->name('users.store');
    Route::get('users/{user}/edit', [AccessUserController::class, 'edit'])->middleware('can:staff.manage')->name('users.edit');
    Route::put('users/{user}', [AccessUserController::class, 'update'])->middleware('can:staff.manage')->name('users.update');

    Route::get('roles', [AccessRoleController::class, 'index'])->middleware('can:roles.view')->name('roles.index');
    Route::get('roles/create', [AccessRoleController::class, 'create'])->middleware('can:roles.manage')->name('roles.create');
    Route::post('roles', [AccessRoleController::class, 'store'])->middleware('can:roles.manage')->name('roles.store');
    Route::get('roles/{role}/edit', [AccessRoleController::class, 'edit'])->middleware('can:roles.manage')->name('roles.edit');
    Route::put('roles/{role}', [AccessRoleController::class, 'update'])->middleware('can:roles.manage')->name('roles.update');
    Route::delete('roles/{role}', [AccessRoleController::class, 'destroy'])->middleware('can:roles.manage')->name('roles.destroy');
});
