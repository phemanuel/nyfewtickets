@extends('layouts.site')
@section('title', 'Retrieve Tickets | NYFEW')
@push('head')
<style>.nyfew-recover{max-width:580px;margin:7vh auto;padding:25px}.nyfew-recover .box{background:#161715;border:1px solid #675638;border-radius:20px;padding:30px;color:#f9f4e8}.nyfew-recover h1{color:#f5dda0}.nyfew-recover p{color:#d9d0ba}.nyfew-recover label{display:block;margin:17px 0 7px}.nyfew-recover input{display:block;width:100%;box-sizing:border-box;background:#0b0c0b;color:#fff;border:1px solid #706447;padding:13px;border-radius:9px}.nyfew-recover button{margin-top:23px}.nyfew-recover .note{padding:12px;background:#263021;border-radius:9px}</style>
@endpush
@section('content')
<main class="nyfew-recover"><div class="box">
<div style="color:#d7b768;letter-spacing:2px;font-size:12px">NYFEW TICKETS · SECURE RECOVERY</div>
<h1>Retrieve your tickets</h1>
<p>Enter the email used at checkout and your order reference. We'll email a time-limited link to your tickets.</p>
@if(session('success'))<p class="note" role="status">{{ session('success') }}</p>@endif
@if($errors->any())<div role="alert" style="color:#fbb4ae">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form action="{{ route('ticket-recovery.request') }}" method="POST">@csrf
<label for="reference">Order reference</label><input id="reference" name="reference" value="{{ old('reference') }}" placeholder="NYFT-..." required maxlength="80">
<label for="email">Buyer email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required>
<button class="btn btn-gold" type="submit">Email my access link →</button>
</form><p style="font-size:13px;margin-top:22px">For security, ticket QR codes are only shown after verifying the emailed link.</p>
</div></main>
@endsection
