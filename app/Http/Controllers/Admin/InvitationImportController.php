<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Services\Tickets\InvitationIssuer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class InvitationImportController extends Controller
{
    public function template()
    {
        $headers = ['full_name','email','phone','organisation','notes'];
        return response()->streamDownload(function () use ($headers) {
            $fp = fopen('php://output','w'); fputcsv($fp,$headers); fclose($fp);
        }, 'nyfew-invitation-template.csv', ['Content-Type'=>'text/csv']);
    }

    public function store(Request $request, InvitationIssuer $issuer)
    {
        $data = $request->validate([
            'event_id'=>'required|integer|exists:events,id','category_id'=>'required|integer|exists:ticket_categories,id',
            'file'=>'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);
        $event = Event::findOrFail($data['event_id']);
        $category = TicketCategory::where('event_id',$event->id)->findOrFail($data['category_id']);
        if ($category->type !== 'complimentary' || !$category->is_active) {
            throw ValidationException::withMessages(['category_id'=>'Select an active complimentary category.']);
        }
        // An all-or-nothing import avoids silent partial successes and confusing duplicate tickets.
        $sheet = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($sheet)<2 || count($sheet)>501) {
            throw ValidationException::withMessages(['file'=>'File must contain 1–500 guests plus a header row.']);
        }
        $headers = array_map(fn($x)=>strtolower(trim((string)$x)),array_shift($sheet));
        $required = ['full_name','email','phone','organisation','notes'];
        if (array_slice($headers,0,5)!==$required) {
            throw ValidationException::withMessages(['file'=>'Use the exact CSV template column names and order.']);
        }
        $rows = []; $seen=[]; $errors=[];
        foreach ($sheet as $index=>$values) {
            if (!array_filter($values,fn($v)=>trim((string)$v)!=='')) continue;
            $row = array_combine($required,array_map(fn($v)=>is_null($v)?null:trim((string)$v),array_slice(array_pad($values,5,null),0,5)));
            $v = Validator::make($row,[
                'full_name'=>'required|string|max:180','email'=>'nullable|email|max:190',
                'phone'=>'nullable|string|max:40','organisation'=>'nullable|string|max:180', 'notes'=>'nullable|string|max:3000',
            ]);
            if ($v->fails() || (!$row['email'] && !$row['phone'])) {
                $errors[]='Row '.($index+2).': '.($v->errors()->first() ?: 'Email or phone is required for duplicate detection.');
                continue;
            }
            if ($row['email']) $row['email']=mb_strtolower($row['email']);
            $key=$row['email'] ? 'e:'.$row['email'] : 'p:'.$row['phone'];
            if (isset($seen[$key])) $errors[]='Row '.($index+2).': Duplicate contact in file.';
            $seen[$key]=true; $rows[]=$row;
        }
        if (!$rows || $errors) throw ValidationException::withMessages(['file'=>implode(' ',array_slice($errors,0,8)) ?: 'No valid guests in file.']);
        $tokens=[];
        DB::transaction(function () use ($rows,$issuer,$event,$category,$request,&$tokens) {
            foreach ($rows as $row) {
                [$ticket,$token]=$issuer->issue($event,$category,$row,$request->user()->id);
                $tokens[]=[$ticket,$token];
            }
        },3);
        // Tokens are intentionally not persisted. Export a one-time printable PDF bundle as a direct response.
        $pdfRows=[];
        foreach ($tokens as [$ticket,$token]) {
            $qr = (new \Endroid\QrCode\Writer\PngWriter())->write(new \Endroid\QrCode\QrCode(route('tickets.verify',['token'=>$token]),size:220,margin:8));
            $pdfRows[]=['ticket'=>$ticket,'qrData'=>base64_encode($qr->getString())];
        }
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.batch-pdf',['rows'=>$pdfRows])
            ->setPaper('a5','portrait')->download('NYFEW-Invitations-'.now()->format('Ymd-His').'.pdf');
    }
}
