<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketOrder;
use App\Services\Tickets\TicketDeliveryService;
use Illuminate\Http\Request;

class TicketDeliveryAdminController extends Controller
{
    public function resend(Request $request, TicketOrder $order, TicketDeliveryService $delivery)
    {
        abort_unless($order->status === 'paid', 403, 'Only fulfilled paid orders can be emailed.');
        $delivery->queueAccessEmail($order);
        return back()->with('success', 'A fresh ticket access link was queued for delivery to the buyer.');
    }
}
