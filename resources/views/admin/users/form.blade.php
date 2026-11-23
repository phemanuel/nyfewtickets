@extends('layouts.admin')
@section('heading', $user->exists ? 'Edit Staff Account' : 'New Staff Account')
@section('content')
@php($editing = $user->exists)
<div class="admin-toolbar"><div><span class="eyebrow">Team & access</span><h2 style="margin:12px 0 5px">{{ $editing ? 'Update staff account' : 'Create staff account' }}</h2><p class="admin-lead">Give each person the access they need, and no more.</p></div><a class="btn btn-outline btn-sm" href="{{ route('admin.users.index') }}">← All staff</a></div>
<div class="admin-card admin-form">
    @if($errors->any())<div class="error-panel" role="alert"><strong>Please correct these errors:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
        @csrf @if($editing) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label for="staff-name">Full name</label><input id="staff-name" name="name" type="text" maxlength="255" required value="{{ old('name', $user->name) }}" autocomplete="name"></div>
            <div class="form-group"><label for="staff-email">Email address</label><input id="staff-email" name="email" type="email" maxlength="255" required value="{{ old('email', $user->email) }}" autocomplete="email"></div>
        </div>
        <div class="form-grid">
            <div class="form-group"><label for="staff-password">{{ $editing ? 'New password (optional)' : 'Temporary password' }}</label><input id="staff-password" name="password" type="password" {{ $editing ? '' : 'required' }} minlength="12" autocomplete="new-password"><p class="form-help">Minimum 12 characters. {{ $editing ? 'Leave blank to keep the current password.' : 'Share securely; never send passwords in a public group.' }}</p></div>
            <div class="form-group"><label for="staff-password-confirmation">Confirm password</label><input id="staff-password-confirmation" name="password_confirmation" type="password" {{ $editing ? '' : 'required' }} minlength="12" autocomplete="new-password"></div>
        </div>
        <div class="access-section-title"><h3>Assign roles</h3><p>Select at least one role. Permissions are inherited from assigned roles.</p></div>
        <div class="access-choice-grid">
            @foreach($roles as $role)
                <label class="access-choice">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, array_map('intval', old('roles', $selectedRoles))))>
                    <span><strong>{{ ucwords(str_replace('-', ' ', $role->name)) }}</strong><small>{{ $role->permissions()->count() }} permissions</small></span>
                </label>
            @endforeach
        </div>
        <div class="access-actions"><button class="btn btn-gold" type="submit">{{ $editing ? 'Save changes' : 'Create account' }}</button><a class="btn btn-quiet" href="{{ route('admin.users.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
