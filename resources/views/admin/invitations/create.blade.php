@extends('layouts.admin')
@section('heading','Issue Invitations')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/invitations.css') }}">
<div class="admin-toolbar"><div><span class="eyebrow">NYFEW / TICKETING</span><h2>Issue guest invitations</h2><p class="admin-lead">Create one ticket or import up to 500 guests in one batch.</p></div><a class="btn btn-outline btn-sm" href="{{ route('admin.invitations.index') }}">← Invitations</a></div>
@if($errors->any())<div class="error-panel" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="inv-grid">
<section class="admin-card admin-form"><h3>Individual invitation</h3><form action="{{ route('admin.invitations.store') }}" method="POST" enctype="multipart/form-data">@csrf
<div class="form-group"><label for="event_id">Event *</label><select required id="event_id" name="event_id" data-event-select><option value="">Select event</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected(old('event_id')==$event->id)>{{ $event->title }}</option>@endforeach</select></div>
<div class="form-group"><label for="category_id">Complimentary category *</label><select id="category_id" name="category_id" required data-category-select><option value="">Select a category</option>@foreach($events as $event)@foreach($event->ticketCategories as $category)<option data-event="{{ $event->id }}" value="{{ $category->id }}" @selected(old('category_id')==$category->id)>{{ $category->name }} ({{ $event->title }})</option>@endforeach
@endforeach</select></div>
<div class="form-group"><label for="full_name">Guest name *</label><input required maxlength="180" id="full_name" name="full_name" value="{{ old('full_name') }}"></div>
<div class="form-grid"><div class="form-group"><label for="email">Email</label><input type="email" maxlength="190" id="email" name="email" value="{{ old('email') }}"></div><div class="form-group"><label for="phone">Phone</label><input maxlength="40" id="phone" name="phone" value="{{ old('phone') }}"></div></div>
<div class="form-group"><label for="organisation">Organisation</label><input maxlength="180" id="organisation" name="organisation" value="{{ old('organisation') }}"></div>
<div class="form-group"><label for="photo">Guest photo (optional, max 2MB)</label><input type="file" accept="image/png,image/jpeg,image/webp" id="photo" name="photo"></div>
<div class="form-group"><label for="notes">Admin notes (private)</label><textarea id="notes" name="notes" rows="2">{{ old('notes') }}</textarea></div>
<button type="submit" class="btn btn-gold">Issue QR ticket →</button></form></section>
@can('tickets.import')<section class="admin-card admin-form"><h3>Bulk invitations</h3><p class="admin-lead">Download the template, fill the guest details, then upload a CSV or Excel file. Every imported guest receives an individual QR ticket in one downloadable PDF.</p>
<a class="btn btn-outline btn-sm" href="{{ route('admin.invitations.template') }}">↓ Download CSV template</a>
<form method="POST" action="{{ route('admin.invitations.import') }}" enctype="multipart/form-data" class="inv-bulk-form">@csrf
<div class="form-group"><label for="import_event">Event *</label><select required name="event_id" id="import_event" data-event-select><option value="">Select event</option>@foreach($events as $event)<option value="{{ $event->id }}">{{ $event->title }}</option>@endforeach</select></div>
<div class="form-group"><label for="import_category">Complimentary category *</label><select required name="category_id" id="import_category" data-category-select><option value="">Select a category</option>@foreach($events as $event)@foreach($event->ticketCategories as $category)<option data-event="{{ $event->id }}" value="{{ $category->id }}">{{ $category->name }} ({{ $event->title }})</option>@endforeach
@endforeach</select></div>
<div class="form-group"><label for="file">Guest list *</label><input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required></div>
<p class="form-help">Maximum 500 guests. Each row requires a name and at least one contact method. Files are validated before any tickets are issued. Save the generated PDF immediately.</p>
<button class="btn btn-gold" type="submit">Import &amp; generate tickets →</button></form></section>
@endcan
</div>
@push('scripts')<script>
document.querySelectorAll('[data-event-select]').forEach(eventSelect=>{
 const categorySelect=eventSelect.closest('form').querySelector('[data-category-select]');
 const sync=()=>{const current=categorySelect.value;let valid=false;Array.from(categorySelect.options).forEach(opt=>{if(!opt.value)return;opt.hidden=opt.dataset.event!==eventSelect.value;opt.disabled=opt.hidden;if(opt.value===current&&!opt.hidden)valid=true;});if(!valid)categorySelect.value='';};
 eventSelect.addEventListener('change',sync);sync();
});
</script>@endpush
@endsection
