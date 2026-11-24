@extends('layouts.site')
@section('title', 'Book Tickets · '.$event->title.' | NYFEW')
@push('head')
<style>
.nyf-sale{max-width:1080px;margin:36px auto 85px;padding:0 20px;color:#f9f4e8}.nyf-sale h1{font-size:clamp(30px,4vw,52px);margin:12px 0}.nyf-sale .sub{color:#b6ad99}.nyf-sale .panel{background:#171915;border:1px solid #494334;border-radius:18px;padding:24px;margin-top:20px}.nyf-sale label{color:#f5e4bb;font-size:14px}.nyf-sale input,.nyf-sale select{width:100%;background:#0e100e;color:#fff4df;border:1px solid #66583d;border-radius:10px;padding:12px;min-height:48px}.nyf-sale button{cursor:pointer}.nyf-sale .two{display:grid;grid-template-columns:1fr 1fr;gap:15px}.nyf-sale .attendee{border:1px solid #514932;border-radius:12px;padding:16px;margin:12px 0}.nyf-sale .gold{border:0;border-radius:11px;background:#d7b768;color:#16120c;font-weight:800;padding:15px 24px}.nyf-sale .add{border:1px solid #d7b768;border-radius:10px;background:transparent;color:#e6c780;padding:11px 16px}.nyf-sale .errors{background:#502b29;padding:14px;border-radius:12px}.nyf-sale .total{color:#e6c780;font-size:23px;font-weight:800}@media(max-width:680px){.nyf-sale .two{grid-template-columns:1fr}}
</style>
@endpush
@section('content')
<main class="nyf-sale">
    <div style="color:#d7b768;letter-spacing:2px;font-size:12px">NYFEW · OFFICIAL TICKETS</div>
    <h1>{{ $event->title }}</h1>
    <p class="sub">{{ $event->starts_at->format('D, d M Y · h:i A') }} · {{ $event->venue ?: 'Venue to be announced' }}</p>
    @if($errors->any())<div class="errors" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!config('nyfew_paystack.enabled'))
        <div class="panel">Online ticket sales are not yet open. Complimentary invitations remain managed by NYFEW administrators.</div>
    @elseif($categories->isEmpty())
        <div class="panel">No paid ticket categories are currently available.</div>
    @else
    <form action="{{ route('sales.checkout',$event) }}" method="POST" id="nyfCheckout">
        @csrf
        <section class="panel"><h2>Purchaser information</h2><div class="two">
            <label>Full name<input name="buyer[name]" value="{{ old('buyer.name') }}" maxlength="180" required></label>
            <label>Email for order correspondence<input name="buyer[email]" type="email" value="{{ old('buyer.email') }}" required></label>
            <label>Phone (optional)<input name="buyer[phone]" value="{{ old('buyer.phone') }}" maxlength="40"></label>
        </div></section>
        <section class="panel"><h2>Attendees</h2><p class="sub">Every attendee receives their own unique QR ticket. Maximum 10 per order.</p>
            <div id="nyfAttendees"></div>
            <button type="button" class="add" id="nyfAdd">+ Add another attendee</button>
            <p class="total" id="nyfTotal">Total: ₦0.00</p>
        </section>
        <button class="gold" type="submit">Continue securely to Paystack →</button>
    </form>
    <template id="nyfTemplate"><div class="attendee">
        <div style="display:flex;justify-content:space-between;gap:12px"><strong class="attendee-title">Attendee</strong><button type="button" class="add remove-attendee">Remove</button></div>
        <div class="two">
            <label>Ticket category<select data-field="category_id" required>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" data-price="{{ $cat->price_kobo }}">{{ $cat->name }} · ₦{{ number_format($cat->price_kobo/100,2) }}</option>
            @endforeach
            </select></label>
            <label>Full name<input data-field="full_name" maxlength="180" required></label>
            <label>Email (optional)<input data-field="email" type="email" maxlength="190"></label>
            <label>Phone (optional)<input data-field="phone" maxlength="40"></label>
        </div></div></template>
    <script>
    (()=>{
      const list=document.getElementById('nyfAttendees'), template=document.getElementById('nyfTemplate');
      function reindex(){[...list.children].forEach((row,i)=>{row.querySelector('.attendee-title').textContent='Attendee '+(i+1);row.querySelectorAll('[data-field]').forEach(field=>field.name='attendees['+i+']['+field.dataset.field+']');});document.getElementById('nyfAdd').disabled=list.children.length>=10;total();}
      function total(){let p=0;list.querySelectorAll('select').forEach(s=>p+=Number(s.selectedOptions[0]?.dataset.price||0));document.getElementById('nyfTotal').textContent='Total: '+new Intl.NumberFormat('en-NG',{style:'currency',currency:'NGN'}).format(p/100);}
      function add(){if(list.children.length>=10)return;let row=template.content.firstElementChild.cloneNode(true);row.querySelector('.remove-attendee').addEventListener('click',()=>{if(list.children.length>1){row.remove();reindex();}});row.querySelector('select').addEventListener('change',total);list.appendChild(row);reindex();}
      document.getElementById('nyfAdd').addEventListener('click',add);add();
    })();
    </script>
    @endif
</main>
@endsection
