<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'venue', 'starts_at', 'ends_at', 'status', 'poster_image'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function ticketCategories(): HasMany
    {
        return $this->hasMany(TicketCategory::class);
    }

    // Public URL for website pages; null when no event-specific image is uploaded.
    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_image ? Storage::disk('public')->url($this->poster_image) : null;
    }

    // Local absolute path for DomPDF / inline mail attachments.
    public function posterFilePath(): ?string
    {
        if (!$this->poster_image || !Storage::disk('public')->exists($this->poster_image)) {
            return null;
        }
        return Storage::disk('public')->path($this->poster_image);
    }
}
