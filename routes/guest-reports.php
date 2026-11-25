<?php

use App\Http\Controllers\Admin\TicketGuestReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:admin.access'])
    ->prefix('admin')->name('admin.guest-reports.')->group(function () {
        Route::get('guest-directory', [TicketGuestReportController::class, 'index'])
            ->middleware('can:ticket-guests.view')->name('index');
        Route::get('guest-directory/export', [TicketGuestReportController::class, 'exportGuests'])
            ->middleware(['can:ticket-reports.export', 'throttle:15,1'])->name('export-guests');
        Route::get('ticket-reports', [TicketGuestReportController::class, 'reports'])
            ->middleware('can:ticket-reports.view')->name('reports');
        Route::get('ticket-reports/export', [TicketGuestReportController::class, 'exportSummary'])
            ->middleware(['can:ticket-reports.export', 'throttle:15,1'])->name('export-summary');
        Route::get('ticket-reports/movements', [TicketGuestReportController::class, 'movements'])
            ->middleware('can:ticket-reports.view')->name('movements');
        Route::get('ticket-reports/movements/export', [TicketGuestReportController::class, 'exportMovements'])
            ->middleware(['can:ticket-reports.export', 'throttle:15,1'])->name('export-movements');
    });
