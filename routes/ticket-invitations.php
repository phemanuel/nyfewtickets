<?php
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\InvitationImportController;
use App\Http\Controllers\TicketVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/ticket/verify/{token}', [TicketVerificationController::class,'show'])
    ->where('token','[A-Za-z0-9]{80}')->middleware('throttle:30,1')->name('tickets.verify');

Route::middleware(['auth','can:admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/invitations/template', [InvitationImportController::class,'template'])->middleware('can:tickets.import')->name('invitations.template');
    Route::post('/invitations/import', [InvitationImportController::class,'store'])->middleware('can:tickets.import')->name('invitations.import');
    Route::get('/invitations', [InvitationController::class,'index'])->middleware('can:tickets.view')->name('invitations.index');
    Route::get('/invitations/create', [InvitationController::class,'create'])->middleware('can:tickets.issue')->name('invitations.create');
    Route::post('/invitations', [InvitationController::class,'store'])->middleware('can:tickets.issue')->name('invitations.store');
    Route::get('/invitations/{ticket}/issued', [InvitationController::class,'issueResult'])->middleware('can:tickets.issue')->name('invitations.issue-result');
    Route::get('/invitations/{ticket}/download', [InvitationController::class,'download'])->middleware('can:tickets.issue')->name('invitations.download');
    Route::post('/invitations/{ticket}/revoke', [InvitationController::class,'revoke'])->middleware('can:tickets.revoke')->name('invitations.revoke');
    Route::get('/ticket-guests/{guest}/edit', [InvitationController::class,'editGuest'])->middleware('can:tickets.issue')->name('ticket-guests.edit');
    Route::put('/ticket-guests/{guest}', [InvitationController::class,'updateGuest'])->middleware('can:tickets.issue')->name('ticket-guests.update');
    Route::get('/ticket-guests/{guest}/photo', [InvitationController::class,'photo'])->middleware('can:tickets.view')->name('ticket-guests.photo');
});
