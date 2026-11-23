<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AccessUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $users = User::query()->with('roles')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users','search'));
    }

    public function create(Request $request)
    {
        $this->onlySuperAdmin($request);
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => Role::where('guard_name','web')->orderBy('name')->get(),
            'selectedRoles' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->onlySuperAdmin($request);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')],
            'password' => ['required','confirmed', Password::min(12)],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['required','integer','distinct', Rule::exists('roles','id')->where('guard_name','web')],
        ]);
        $roles = Role::query()->whereIn('id',$data['roles'])->where('guard_name','web')->get();
        abort_unless($roles->count() === count($data['roles']), 422);
        DB::transaction(function () use ($data, $roles) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
            ]);
            $user->syncRoles($roles);
        });
        return redirect()->route('admin.users.index')->with('success','Staff account created successfully.');
    }

    public function edit(Request $request, User $user)
    {
        $this->onlySuperAdmin($request);
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::where('guard_name','web')->orderBy('name')->get(),
            'selectedRoles' => $user->roles()->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->onlySuperAdmin($request);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','confirmed', Password::min(12)],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['required','integer','distinct', Rule::exists('roles','id')->where('guard_name','web')],
        ]);
        $roles = Role::query()->whereIn('id',$data['roles'])->where('guard_name','web')->get();
        abort_unless($roles->count() === count($data['roles']), 422);

        DB::transaction(function () use ($user, $data, $roles) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $removesSuper = $lockedUser->hasRole('super-admin') && ! $roles->contains('name','super-admin');
            if ($removesSuper) {
                $superRole = Role::findByName('super-admin', 'web');
                $count = DB::table('model_has_roles')
                    ->where('role_id', $superRole->id)
                    ->where('model_type', $lockedUser->getMorphClass())
                    ->count();
                if ($count <= 1) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'roles' => 'You cannot remove the last Super Admin.',
                    ]);
                }
            }
            $lockedUser->name = $data['name'];
            $lockedUser->email = strtolower($data['email']);
            if (! empty($data['password'])) {
                $lockedUser->password = Hash::make($data['password']);
            }
            $lockedUser->save();
            $lockedUser->syncRoles($roles);
        });
        return redirect()->route('admin.users.index')->with('success','Staff account updated.');
    }

    private function onlySuperAdmin(Request $request): void
    {
        // Prevent a delegated staff manager from assigning themselves super-admin.
        abort_unless($request->user()?->hasRole('super-admin'), 403);
    }
}
