@extends('layouts.admin')
@section('heading','Ticket Categories')
@section('content')
<div class="admin-toolbar">
    <div>
        <span class="eyebrow">{{ $event->title }}</span>
        <h2 style="margin:12px 0 5px">Ticket categories</h2>
        <p class="admin-lead">Set ticket prices and allocations for this event.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-outline btn-sm" href="{{ route('admin.events.index') }}">← Events</a>
        @can('events.create')<a class="btn btn-gold" href="{{ route('admin.events.categories.create', $event) }}">+ Add category</a>@endcan
    </div>
</div>
<div class="admin-card">
 <div class="panel-head"><h3 class="panel-title">Configured categories</h3><span class="table-muted">{{ $categories->count() }} categories</span></div>
 <div class="table-wrap"><table><thead><tr><th>Category</th><th>Access</th><th>Price</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody>
 @forelse($categories as $category)
 <tr>
 <td><strong>{{ $category->name }}</strong><div class="table-muted">{{ $category->description ?: $category->slug }}</div></td>
 <td>{{ $category->type === 'paid' ? 'Paid' : 'Complimentary' }}</td>
 <td>{{ $category->type === 'paid' ? '₦'.number_format($category->price_kobo/100,2) : 'Free' }}</td>
 <td>{{ number_format($category->capacity) }}</td>
 <td><span class="status {{ $category->is_active ? 'published' : '' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
 <td>@can('events.edit')<a class="btn btn-outline btn-sm" href="{{ route('admin.events.categories.edit', [$event, $category]) }}">Edit</a>@else — @endcan</td>
 </tr>
 @empty
 <tr><td colspan="6"><div class="admin-empty"><strong>No ticket categories yet</strong><span>Add a paid category or complimentary invitation allocation.</span></div></td></tr>
 @endforelse
 </tbody></table></div>
</div>
<p class="form-help" style="margin-top:18px">Capacity records the configured allocation. Actual remaining inventory and ticket issuance will be connected in the next module.</p>
@endsection
