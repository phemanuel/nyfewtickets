<?php
use App\Http\Controllers\TicketSaleController;
use App\Http\Controllers\Admin\TicketSalesAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:30,1')->group(function () {
    Route::get('/events/{event:slug}/tickets', [TicketSaleController::class,'show'])->name('sales.show');
    Route::post('/events/{event:slug}/tickets', [TicketSaleController::class,'checkout'])->middleware('throttle:5,1')->name('sales.checkout');
    Route::get('/tickets/payment/callback', [TicketSaleController::class,'callback'])->name('sales.callback');
    Route::get('/tickets/orders/{reference}', [TicketSaleController::class,'receipt'])->name('sales.receipt');
    Route::get('/tickets/orders/{reference}/download/{ticket}', [TicketSaleController::class,'download'])->name('sales.ticket-download');
});
Route::post('/tickets/payment/webhook', [TicketSaleController::class,'webhook'])->name('sales.webhook');

Route::middleware(['auth','can:admin.access','can:ticket-sales.view'])
    ->get('/admin/ticket-sales', [TicketSalesAdminController::class,'index'])->name('admin.ticket-sales.index');
