<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Event,TicketOrder};
use Illuminate\Http\Request;

class TicketSalesAdminController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderByDesc('starts_at')->get();
        $orders = TicketOrder::with('event')->withCount('attendees')
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id',$request->integer('event_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status',$request->input('status')))
            ->latest()->paginate(25)->withQueryString();
        $query = TicketOrder::query()->when($request->filled('event_id'), fn ($q) => $q->where('event_id',$request->integer('event_id')));
        $revenue = (clone $query)->whereIn('status',['paid','paid_unfulfilled'])->sum('amount_kobo');
        $paidCount = (clone $query)->where('status','paid')->count();
        $attentionCount = (clone $query)->where('status','paid_unfulfilled')->count();
        return view('admin.sales.index', compact('events','orders','revenue','paidCount','attentionCount'));
    }
}
