@extends('layouts.admin')
@section('heading','Create Event')
@section('content')
<div class="card" style="max-width:850px">
  <h2 style="margin-top:0">Create an event</h2>
  @if($errors->any())<div class="admin-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
  <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
    @csrf
    <label for="title">Event name</label><input id="title" name="title" value="{{ old('title') }}" required maxlength="180">
    <p style="color:#b4a88c;font-size:.85rem">The URL slug is generated automatically.</p>
    <label for="poster_image">Event artwork / poster (PNG or JPG, max 5 MB)</label>
    <input type="file" id="poster_image" name="poster_image" accept="image/jpeg,image/png">
    <p style="color:#b4a88c;font-size:.85rem">Portrait artwork is recommended. This image will also appear in ticket PDFs and email messages.</p>
    <label for="venue">Venue</label><input id="venue" name="venue" value="{{ old('venue') }}">
    <label for="starts_at">Starts at (local event time)</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required>
    <label for="ends_at">Ends at</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}">
    <label for="description">Description</label><textarea id="description" name="description" rows="5">{{ old('description') }}</textarea>
    <label for="status">Visibility</label><select id="status" name="status"><option value="draft" @selected(old('status')==='draft')>Draft</option><option value="published" @selected(old('status')==='published')>Published</option></select>
    <button class="btn btn-gold" type="submit">Create event</button>
  </form>
</div>
@endsection
