@extends('layouts.admin')
@section('heading', 'Staff Accounts')
@section('content')
<div class="admin-toolbar">
    <div>
        <span class="eyebrow">Team & access</span>
        <h2 style="margin:12px 0 5px">Staff accounts</h2>
        <p class="admin-lead">Manage who can access the NYFEW administration.</p>
    </div>
    @role('super-admin')
        <a href="{{ route('admin.users.create') }}" class="btn btn-gold">+ Create staff account</a>
    @endrole
</div>
<div class="admin-card">
    <form method="GET" action="{{ route('admin.users.index') }}" class="access-filter" role="search">
        <label for="staff-search" class="sr-only">Search staff</label>
        <input type="search" name="q" id="staff-search" value="{{ $search }}" placeholder="Search staff name or email">
        <button class="btn btn-outline btn-sm" type="submit">Search</button>
        @if($search !== '') <a href="{{ route('admin.users.index') }}" class="btn btn-quiet btn-sm">Clear</a> @endif
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Staff member</th><th>Email address</th><th>Assigned roles</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($users as $person)
                    <tr>
                        <td><strong>{{ $person->name }}</strong></td>
                        <td>{{ $person->email }}</td>
                        <td><div class="access-badges">@forelse($person->roles as $role)<span class="access-pill">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>@empty<span class="table-muted">No roles</span>@endforelse</div></td>
                        <td>@role('super-admin') <a class="access-text-link" href="{{ route('admin.users.edit', $person) }}">Edit account →</a> @else <span class="table-muted">View only</span> @endrole</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="admin-empty"><strong>No accounts found</strong>Try another search or create your first staff account.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $users->links() }}</div>
</div>
@endsection
