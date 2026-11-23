<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketVerificationController extends Controller
{
    public function show(string $token, Request $request)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{80}$/D', $token), 404);
        $ticket = Ticket::query()->with(['guest','category','event'])->where('token_hash',hash('sha256',$token))->firstOrFail();
        $staff = $request->user()?->can('tickets.verify') ?? false;
        return view('tickets.verify', compact('ticket','staff'));
    }
}
