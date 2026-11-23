<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketGuest extends Model
{
    protected $fillable = ['full_name','email','phone','organisation','photo_path','notes'];
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
}
