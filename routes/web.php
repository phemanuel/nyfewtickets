<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;



Route::get('/', [PublicSiteController::class, 'index'])->name('home');
Route::get('/events', [PublicSiteController::class, 'events'])->name('events.index');
Route::get('/events/{event:slug}', [PublicSiteController::class, 'show'])->name('events.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    });
    Route::middleware(['auth', 'can:admin.access'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/events', [EventController::class, 'index'])->middleware('can:events.view')->name('events.index');
        Route::get('/events/create', [EventController::class, 'create'])->middleware('can:events.create')->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->middleware('can:events.create')->name('events.store');
        Route::get('/staff', [StaffController::class, 'index'])->middleware('can:staff.view')->name('staff.index');
    });    
});

// Laravel's default auth middleware seeks the named `login` route.
Route::redirect('/login', '/admin/login')->name('login');


require __DIR__.'/admin-access.php';
require __DIR__.'/ticket-categories.php';
require __DIR__.'/ticket-invitations.php';
require __DIR__.'/ticket-checkins.php';
