<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;

class DashboardController extends Controller
{
    public function index() { return view('admin.dashboard', ['eventsCount' => Event::count(), 'publishedCount' => Event::where('status','published')->count(), 'staffCount' => User::permission('admin.access')->count(), 'latestEvents' => Event::latest()->take(5)->get()]); }
}
