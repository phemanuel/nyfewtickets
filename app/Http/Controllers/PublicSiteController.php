<?php
namespace App\Http\Controllers;

use App\Models\Event;

class PublicSiteController extends Controller
{
    public function index() { return view('home', ['events' => Event::query()->where('status', 'published')->orderBy('starts_at')->take(3)->get()]); }
    public function events() { return view('events.index', ['events' => Event::query()->where('status', 'published')->orderBy('starts_at')->paginate(9)]); }
    public function show(Event $event) { abort_unless($event->status === 'published', 404); return view('events.show', compact('event')); }
}
