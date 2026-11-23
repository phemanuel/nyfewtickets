<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessRoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->where('guard_name','web')->withCount(['permissions','users'])->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->onlySuperAdmin($request);
        return view('admin.roles.form', [
            'role' => new Role(['guard_name' => 'web']),
            'permissions' => $this->permissionGroups(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->onlySuperAdmin($request);
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
            $role->syncPermissions($data['permissions'] ?? []);
        });
        return redirect()->route('admin.roles.index')->with('success','Role created successfully.');
    }

    public function edit(Request $request, Role $role)
    {
        $this->onlySuperAdmin($request);
        abort_unless($role->guard_name === 'web', 404);
        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => $this->permissionGroups(),
            'selectedPermissions' => $role->permissions()->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->onlySuperAdmin($request);
        abort_unless($role->guard_name === 'web', 404);
        abort_if($role->name === 'super-admin', 403, 'The Super Admin role is protected.');
        $data = $this->validated($request, $role);
        DB::transaction(function () use ($role, $data) {
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
        });
        return redirect()->route('admin.roles.index')->with('success','Role updated successfully.');
    }

    public function destroy(Request $request, Role $role)
    {
        $this->onlySuperAdmin($request);
        abort_unless($role->guard_name === 'web', 404);
        abort_if($role->name === 'super-admin', 403, 'The Super Admin role is protected.');
        abort_if($role->users()->exists(), 422, 'This role is assigned to users and cannot be deleted.');
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success','Unused role deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $rule = Rule::unique('roles', 'name')->where('guard_name','web');
        if ($role) $rule->ignore($role->id);
        return $request->validate([
            'name' => ['required','string','min:2','max:100','regex:/^[a-z][a-z0-9-]*$/', $rule, Rule::notIn(['super-admin'])],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['required','integer','distinct', Rule::exists('permissions','id')->where('guard_name','web')],
        ]);
    }

    private function permissionGroups(): array
    {
        return Permission::query()->where('guard_name','web')->orderBy('name')->get()->groupBy(
            fn (Permission $permission) => ucfirst(explode('.', $permission->name)[0])
        )->all();
    }

    private function onlySuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('super-admin'), 403);
    }
}
