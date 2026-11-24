<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketGuest;
use App\Services\Tickets\InvitationIssuer;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderByDesc('starts_at')->get();
        $event = $request->filled('event_id') ? Event::findOrFail($request->integer('event_id')) : $events->first();
        $tickets = Ticket::with(['event','category','guest'])->where('kind','invitation')
            ->when($event, fn ($q) => $q->where('event_id',$event->id))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($inner) use ($request) {
                $term = '%'.$request->input('search').'%';
                $inner->where('number','like',$term)->orWhereHas('guest',fn ($guest) => $guest->where('full_name','like',$term)->orWhere('email','like',$term)->orWhere('phone','like',$term));
            }))->latest()->paginate(20)->withQueryString();
        return view('admin.invitations.index', compact('events','event','tickets'));
    }

    public function create()
    {
        $events = Event::with(['ticketCategories'=>fn ($q) => $q->where('type','complimentary')->where('is_active',true)->orderBy('sort_order')])->orderByDesc('starts_at')->get();
        return view('admin.invitations.create',compact('events'));
    }

    public function store(Request $request, InvitationIssuer $issuer)
    {
        $data = $request->validate([
            'event_id'=>'required|integer|exists:events,id','category_id'=>'required|integer|exists:ticket_categories,id',
            'full_name'=>'required|string|max:180','email'=>'nullable|email|max:190','phone'=>'nullable|string|max:40',
            'organisation'=>'nullable|string|max:180','notes'=>'nullable|string|max:3000',
            'photo'=>'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);
        $event = Event::findOrFail($data['event_id']);
        $category = TicketCategory::where('event_id',$event->id)->findOrFail($data['category_id']);
        $guestData = collect($data)->only(['full_name','email','phone','organisation','notes'])->all();
        if (!empty($guestData['email'])) $guestData['email'] = mb_strtolower(trim($guestData['email']));
        if ($request->hasFile('photo')) $guestData['photo_path'] = $request->file('photo')->store('ticket-guest-photos','local');
        try { [$ticket,$token] = $issuer->issue($event,$category,$guestData,$request->user()->id);
            $ticket->forceFill(['qr_token_encrypted'=>\Illuminate\Support\Facades\Crypt::encryptString($token)])->save();
        }
        catch (\Throwable $e) {
            if (isset($guestData['photo_path'])) Storage::disk('local')->delete($guestData['photo_path']);
            throw $e;
        }
        return redirect()->route('admin.invitations.issue-result', ['ticket'=>$ticket->id, 'token'=>$token])
            ->with('success','Invitation issued. Download the PDF now; the QR token is not stored in readable form.');
    }

    public function editGuest(TicketGuest $guest)
    {
        return view('admin.invitations.edit-guest', compact('guest'));
    }

    public function updateGuest(Request $request, TicketGuest $guest)
    {
        $data = $request->validate([
            'full_name'=>'required|string|max:180','email'=>'nullable|email|max:190',
            'phone'=>'nullable|string|max:40','organisation'=>'nullable|string|max:180',
            'notes'=>'nullable|string|max:3000','photo'=>'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);
        if (!empty($data['email'])) $data['email']=mb_strtolower(trim($data['email']));
        if ($request->hasFile('photo')) {
            $data['photo_path']=$request->file('photo')->store('ticket-guest-photos','local');
            $previous=$guest->photo_path;
        }
        unset($data['photo']);
        $guest->update($data);
        if (!empty($previous)) Storage::disk('local')->delete($previous);
        return redirect()->route('admin.invitations.index')->with('success','Guest details updated.');
    }

    public function issueResult(Ticket $ticket, Request $request)
    {
        abort_unless($ticket->kind === 'invitation' && hash_equals($ticket->token_hash,hash('sha256',(string)$request->query('token'))),404);
        $ticket->load('event','guest','category');
        return view('admin.invitations.issued', ['ticket'=>$ticket, 'token'=>$request->query('token')]);
    }

    public function download(Ticket $ticket, Request $request)
    {
        $token = (string) $request->query('token');
        abort_unless($ticket->kind === 'invitation' && hash_equals($ticket->token_hash,hash('sha256',$token)),404);
        $ticket->load('event','guest','category');
        $url = route('tickets.verify', ['token'=>$token]);
        $qr = (new PngWriter())->write(new QrCode($url, size: 270, margin: 12));
        $qrData = base64_encode($qr->getString());
        $pdf = Pdf::loadView('tickets.pdf',compact('ticket','qrData'))->setPaper('a4','landscape');
        return $pdf->download($ticket->number.'.pdf');
    }

    public function photo(TicketGuest $guest)
    {
        abort_if(!$guest->photo_path || !Storage::disk('local')->exists($guest->photo_path),404);
        return Storage::disk('local')->response($guest->photo_path);
    }

    public function revoke(Ticket $ticket)
    {
        abort_unless($ticket->kind === 'invitation',404);
        $ticket->update(['status'=>'revoked','revoked_at'=>now()]);
        return back()->with('success','Invitation revoked. Its QR code is no longer valid.');
    }
}
