<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public function ticketCategories(): HasMany { return $this->hasMany(TicketCategory::class); }

    protected $fillable = ['title','slug','description','venue','starts_at','ends_at','status'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
}
