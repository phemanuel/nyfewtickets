@extends('layouts.admin')
@section('heading', $role->exists ? 'Edit Role' : 'Create Role')
@section('content')
@php($editing = $role->exists)
@php($protected = $editing && $role->name === 'super-admin')
<div class="admin-toolbar"><div><span class="eyebrow">Access governance</span><h2 style="margin:12px 0 5px">{{ $protected ? 'Super Admin permissions' : ($editing ? 'Edit role' : 'Create a new role') }}</h2><p class="admin-lead">Choose which areas of the system members of this role may access.</p></div><a href="{{ route('admin.roles.index') }}" class="btn btn-outline btn-sm">← All roles</a></div>
<div class="admin-card admin-form">
    @if($errors->any())<div class="error-panel" role="alert"><strong>Please correct:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($protected)<div class="info-strip">The Super Admin role is protected from editing and deletion.</div>@endif
    <form method="POST" action="{{ $editing ? route('admin.roles.update',$role) : route('admin.roles.store') }}">
        @csrf @if($editing) @method('PUT') @endif
        <div class="form-group"><label for="role-name">Role identifier</label><input id="role-name" name="name" type="text" required maxlength="100" pattern="[a-z][a-z0-9-]*" placeholder="e.g. event-coordinator" value="{{ old('name',$role->name) }}" @disabled($protected)><p class="form-help">Lowercase letters, numbers and hyphens only. Example: <code>event-coordinator</code></p></div>
        <div class="access-section-title"><h3>Permission matrix</h3><p>Select what users assigned to this role can do.</p></div>
        @foreach($permissions as $group => $items)
            <fieldset class="access-permission-group"><legend>{{ $group }}</legend><div class="access-choice-grid">
                @foreach($items as $permission)
                    <label class="access-choice"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id,array_map('intval',old('permissions',$selectedPermissions)))) @disabled($protected)>
                        <span><strong>{{ $permission->name }}</strong></span></label>
                @endforeach
            </div></fieldset>
        @endforeach
        @unless($protected)<div class="access-actions"><button type="submit" class="btn btn-gold">{{ $editing ? 'Save role' : 'Create role' }}</button><a href="{{ route('admin.roles.index') }}" class="btn btn-quiet">Cancel</a></div>@endunless
    </form>
    @if($editing && ! $protected)
        <div class="access-danger-zone"><div><strong>Delete role</strong><p class="form-help">Allowed only when no staff account is assigned to this role.</p></div>
            <form method="POST" action="{{ route('admin.roles.destroy',$role) }}" onsubmit="return confirm('Delete this role? This cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="btn btn-quiet btn-sm">Delete role</button></form>
        </div>
    @endif
</div>
@endsection
