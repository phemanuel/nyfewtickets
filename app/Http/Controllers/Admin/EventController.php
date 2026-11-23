<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        return view('admin.events.index', ['events' => Event::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        // Slug is generated on the server; never trust a slug submitted by the browser.
        $baseSlug = Str::slug($validated['title']);
        if ($baseSlug === '') {
            $baseSlug = 'event';
        }
        $baseSlug = Str::limit($baseSlug, 170, '');
        $slug = $baseSlug;
        $suffix = 2;

        while (Event::where('slug', $slug)->exists()) {
            $ending = '-' . $suffix++;
            $slug = substr($baseSlug, 0, 180 - strlen($ending)) . $ending;
        }

        $validated['slug'] = $slug;
        Event::create($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }
}
