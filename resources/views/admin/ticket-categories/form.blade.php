@extends('layouts.admin')
@section('heading', $category->exists ? 'Edit Ticket Category' : 'New Ticket Category')
@section('content')
<div class="admin-toolbar">
 <div><span class="eyebrow">{{ $event->title }}</span><h2 style="margin:12px 0 5px">{{ $category->exists ? 'Edit category' : 'Create ticket category' }}</h2><p class="admin-lead">Configure access, pricing, availability and the total allocation.</p></div>
 <a class="btn btn-outline btn-sm" href="{{ route('admin.events.categories.index', $event) }}">← Categories</a>
</div>
<div class="admin-card admin-form">
@if($errors->any())<div class="error-panel" role="alert"><strong>Check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $category->exists ? route('admin.events.categories.update', [$event,$category]) : route('admin.events.categories.store', $event) }}">
@csrf @if($category->exists) @method('PUT') @endif
<div class="form-group"><label for="name">Category name *</label><input id="name" name="name" maxlength="120" required value="{{ old('name', $category->name) }}" placeholder="e.g. VIP, Regular, Special Guest"><p class="form-help">The public-facing name; a unique slug is created automatically.</p></div>
<div class="form-group"><label for="description">Description</label><textarea name="description" id="description" rows="3" maxlength="2000" placeholder="What does this ticket include?">{{ old('description',$category->description) }}</textarea></div>
<div class="form-grid">
 <div class="form-group"><label for="type">Access type *</label><select name="type" id="type" required><option value="paid" @selected(old('type', $category->type ?: 'paid')==='paid')>Paid ticket</option><option value="complimentary" @selected(old('type', $category->type)==='complimentary')>Complimentary invitation</option></select></div>
 <div class="form-group" id="price-group"><label for="price_naira">Price (₦) *</label><input id="price_naira" name="price_naira" type="number" min="0.01" step="0.01" inputmode="decimal" value="{{ old('price_naira', $category->exists && $category->type === 'paid' ? $category->price_naira : '') }}" placeholder="e.g. 15000"><p class="form-help">Amount in naira. Stored securely in kobo.</p></div>
</div>
<div class="form-grid">
 <div class="form-group"><label for="capacity">Total ticket allocation *</label><input id="capacity" name="capacity" type="number" min="1" max="10000000" required value="{{ old('capacity',$category->capacity) }}" placeholder="e.g. 200"></div>
 <div class="form-group"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order',$category->sort_order ?? 0) }}"></div>
</div>
<div class="form-grid">
 <div class="form-group"><label for="sales_start_at">Sales/opening date (optional)</label><input id="sales_start_at" name="sales_start_at" type="datetime-local" value="{{ old('sales_start_at', $category->sales_start_at?->format('Y-m-d\TH:i')) }}"></div>
 <div class="form-group"><label for="sales_end_at">Sales/closing date (optional)</label><input id="sales_end_at" name="sales_end_at" type="datetime-local" value="{{ old('sales_end_at', $category->sales_end_at?->format('Y-m-d\TH:i')) }}"></div>
</div>
<div class="form-group"><label style="display:inline-flex;align-items:center;gap:10px"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))> Active category</label><p class="form-help">Inactive categories won't be eligible for future public purchases or invitations.</p></div>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><button class="btn btn-gold" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }} →</button><a class="btn btn-quiet" href="{{ route('admin.events.categories.index',$event) }}">Cancel</a></div>
</form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const select=document.getElementById('type'), group=document.getElementById('price-group'), input=document.getElementById('price_naira');
 const refresh=()=>{const paid=select.value==='paid';group.hidden=!paid;input.required=paid;input.disabled=!paid;};
 select.addEventListener('change',refresh); refresh();
});
</script>
@endpush
@endsection
