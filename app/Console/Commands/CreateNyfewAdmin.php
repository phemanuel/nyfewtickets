<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNyfewAdmin extends Command
{
    protected $signature = 'nyfew:create-admin';
    protected $description = 'Interactively create the first NYFEW super administrator';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Administrator name'));
        $email = strtolower(trim((string) $this->ask('Administrator email')));
        $password = (string) $this->secret('Password (min 12 characters)');
        $confirm = (string) $this->secret('Confirm password');
        if ($password !== $confirm) { $this->error('Passwords do not match.'); return self::FAILURE; }
        $validation = Validator::make(compact('name','email','password'),[
            'name'=>['required','string','max:255'],
            'email'=>['required','email','max:255','unique:users,email'],
            'password'=>['required','string','min:12'],
        ]);
        if ($validation->fails()) { foreach ($validation->errors()->all() as $error) $this->error($error); return self::FAILURE; }
        if (! \Spatie\Permission\Models\Role::where('name','super-admin')->exists()) {
            $this->error('Run php artisan db:seed --class=RolesAndPermissionsSeeder first.');
            return self::FAILURE;
        }
        $admin = User::create(['name'=>$name,'email'=>$email,'password'=>Hash::make($password)]);
        $admin->assignRole('super-admin');
        $this->info('Super administrator created successfully.');
        return self::SUCCESS;
    }
}
