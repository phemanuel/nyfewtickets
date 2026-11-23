@extends('layouts.admin')
@section('heading','Edit Guest')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/invitations.css') }}">
<div class="admin-toolbar"><div><span class="eyebrow">NYFEW / GUEST DIRECTORY</span><h2>Edit guest</h2><p class="admin-lead">Updates to this guest appear on all associated tickets.</p></div><a class="btn btn-outline btn-sm" href="{{ route('admin.invitations.index') }}">← Invitations</a></div>
@if($errors->any())<div class="error-panel"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="admin-card admin-form"><form method="POST" action="{{ route('admin.ticket-guests.update',$guest) }}" enctype="multipart/form-data">@csrf @method('PUT')
<div class="form-group"><label for="full_name">Guest name *</label><input id="full_name" name="full_name" required maxlength="180" value="{{ old('full_name',$guest->full_name) }}"></div>
<div class="form-grid"><div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="{{ old('email',$guest->email) }}"></div><div class="form-group"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone',$guest->phone) }}"></div></div>
<div class="form-group"><label for="organisation">Organisation</label><input id="organisation" name="organisation" value="{{ old('organisation',$guest->organisation) }}"></div>
<div class="form-group"><label for="notes">Private notes</label><textarea id="notes" name="notes" rows="3">{{ old('notes',$guest->notes) }}</textarea></div>
@if($guest->photo_path)<img src="{{ route('admin.ticket-guests.photo',$guest) }}" alt="Guest photo" style="max-height:120px;border-radius:10px;display:block;margin-bottom:12px">@endif
<div class="form-group"><label for="photo">Replace photo (optional)</label><input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp"></div>
<button class="btn btn-gold" type="submit">Save guest details →</button></form></div>
@endsection
