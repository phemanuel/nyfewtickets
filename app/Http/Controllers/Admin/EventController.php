<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'poster_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'remove_poster' => ['sometimes', 'boolean'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        // Don't trust slugs in form input; preserve existing automatic uniqueness behaviour.
        $base = substr(Str::slug($data['title']) ?: 'event', 0, 170);
        $slug = $base;
        for ($suffix = 2; Event::where('slug', $slug)->exists(); $suffix++) {
            $ending = '-'.$suffix;
            $slug = substr($base, 0, 180 - strlen($ending)).$ending;
        }
        $data['slug'] = $slug;
        unset($data['remove_poster'], $data['poster_image']);
        if ($request->hasFile('poster_image')) {
            $data['poster_image'] = $request->file('poster_image')->store('event-posters', 'public');
        }
        try {
            Event::create($data);
        } catch (\Throwable $exception) {
            if (!empty($data['poster_image'])) Storage::disk('public')->delete($data['poster_image']);
            throw $exception;
        }
        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validated($request);
        $oldPoster = $event->poster_image;
        unset($data['poster_image'], $data['remove_poster']);
        $newPoster = null;
        if ($request->hasFile('poster_image')) {
            $newPoster = $request->file('poster_image')->store('event-posters', 'public');
            $data['poster_image'] = $newPoster;
        } elseif ($request->boolean('remove_poster')) {
            $data['poster_image'] = null;
        }
        try {
            $event->update($data); // Keeps existing slug stable so purchased ticket URLs do not change.
        } catch (\Throwable $exception) {
            if ($newPoster) Storage::disk('public')->delete($newPoster);
            throw $exception;
        }
        if ($oldPoster && array_key_exists('poster_image', $data) && $oldPoster !== $data['poster_image']) {
            Storage::disk('public')->delete($oldPoster);
        }
        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }
}
