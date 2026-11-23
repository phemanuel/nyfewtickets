@extends('layouts.admin')
@section('heading', 'Roles & Permissions')
@section('content')
<div class="admin-toolbar">
    <div><span class="eyebrow">Access governance</span><h2 style="margin:12px 0 5px">Roles & permissions</h2><p class="admin-lead">Create reusable permission sets and assign them to your staff.</p></div>
    @role('super-admin')<a href="{{ route('admin.roles.create') }}" class="btn btn-gold">+ Create role</a>@endrole
</div>
<div class="admin-card">
    <div class="table-wrap"><table>
        <thead><tr><th>Role</th><th>Permissions</th><th>Staff assigned</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($roles as $role)
                <tr><td><strong>{{ ucwords(str_replace('-', ' ', $role->name)) }}</strong>@if($role->name === 'super-admin') <span class="access-pill">Protected</span>@endif</td>
                    <td>{{ $role->permissions_count }}</td><td>{{ $role->users_count }}</td>
                    <td>@role('super-admin')<a href="{{ route('admin.roles.edit',$role) }}" class="access-text-link">{{ $role->name === 'super-admin' ? 'View permissions' : 'Manage role' }} →</a>@else <span class="table-muted">View only</span> @endrole</td></tr>
            @empty<tr><td colspan="4"><div class="admin-empty">No roles found.</div></td></tr>@endforelse
        </tbody>
    </table></div>
</div>
@endsection
