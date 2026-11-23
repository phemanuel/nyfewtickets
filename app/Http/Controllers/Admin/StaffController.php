<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index() { return view('admin.staff', ['staff' => User::role(['super-admin','ticket-manager','gate-officer'])->orderBy('name')->get()]); }
}
