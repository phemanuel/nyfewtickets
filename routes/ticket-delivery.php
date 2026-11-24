<?php
use App\Http\Controllers\TicketRecoveryController;
use App\Http\Controllers\Admin\TicketDeliveryAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets/retrieve', [TicketRecoveryController::class, 'index'])->name('ticket-recovery.index');
Route::post('/tickets/retrieve', [TicketRecoveryController::class, 'requestLink'])
    ->middleware('throttle:5,1')->name('ticket-recovery.request');
Route::get('/tickets/access/{token}', [TicketRecoveryController::class, 'open'])
    ->middleware('throttle:30,1')->name('ticket-recovery.open');
Route::post('/tickets/access/{token}', [TicketRecoveryController::class, 'consume'])
    ->middleware('throttle:10,1')->name('ticket-recovery.consume');
Route::post('/admin/ticket-orders/{order}/email', [TicketDeliveryAdminController::class, 'resend'])
    ->middleware(['auth', 'can:admin.access', 'can:ticket-sales.view', 'throttle:10,1'])
    ->name('admin.ticket-delivery.resend');
