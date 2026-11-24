@extends('layouts.admin')
@section('heading','Edit Event')
@section('content')
<div class="card" style="max-width:850px">
  <h2 style="margin-top:0">Edit event</h2>
  @if($errors->any())<div class="admin-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
  <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <label for="title">Event name</label><input id="title" name="title" value="{{ old('title', $event->title) }}" required maxlength="180">
    <p style="color:#b4a88c;font-size:.85rem">The URL slug is generated automatically.</p>
    <label for="poster_image">Event artwork / poster (PNG or JPG, max 5 MB)</label>
    <input type="file" id="poster_image" name="poster_image" accept="image/jpeg,image/png">
    <p style="color:#b4a88c;font-size:.85rem">Upload a replacement image only if you want to change the current artwork.</p>
    @if($event->poster_url)<img src="{{ $event->poster_url }}" alt="Current artwork" style="width:160px;max-height:210px;object-fit:contain;display:block;margin:14px 0;border:1px solid #796538;border-radius:10px">@endif
    @if($event->poster_image)<label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="remove_poster" value="1" style="width:auto"> Remove existing artwork</label>@endif
    <label for="venue">Venue</label><input id="venue" name="venue" value="{{ old('venue', $event->venue) }}">
    <label for="starts_at">Starts at (local event time)</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required>
    <label for="ends_at">Ends at</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}">
    <label for="description">Description</label><textarea id="description" name="description" rows="5">{{ old('description', $event->description) }}</textarea>
    <label for="status">Visibility</label><select id="status" name="status"><option value="draft" @selected(old('status', $event->status)==='draft')>Draft</option><option value="published" @selected(old('status', $event->status)==='published')>Published</option></select>
    <button class="btn btn-gold" type="submit">Save changes</button>
  </form>
</div>
@endsection
