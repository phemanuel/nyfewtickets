<?php
use App\Http\Controllers\Admin\TicketDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','can:admin.access'])->prefix('admin')->name('admin.ticket-docs.')->group(function () {
    Route::get('ticket-orders/{order}', [TicketDocumentController::class,'order'])
        ->middleware('can:ticket-sales.view')->name('order');
    Route::get('ticket-orders/{order}/tickets', [TicketDocumentController::class,'all'])
        ->middleware('can:ticket-sales.view')->name('all');
    Route::get('ticket-orders/{order}/tickets/{ticket}', [TicketDocumentController::class,'one'])
        ->middleware('can:ticket-sales.view')->name('one');
    Route::get('ticket-orders/{order}/receipt', [TicketDocumentController::class,'receipt'])
        ->middleware('can:ticket-sales.view')->name('receipt');
    Route::get('invitation-tickets/{ticket}/download', [TicketDocumentController::class,'invitation'])
        ->middleware('can:tickets.view')->name('invitation');
    Route::post('invitation-tickets/{ticket}/reissue', [TicketDocumentController::class,'reissue'])
        ->middleware('can:tickets.issue')->name('reissue');
});
