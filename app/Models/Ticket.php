<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = ['event_id','ticket_category_id','ticket_guest_id','issued_by','number','token_hash','kind','status','issued_at','revoked_at'];
    protected $casts = ['issued_at'=>'datetime','revoked_at'=>'datetime','checked_in_at'=>'datetime'];
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function category(): BelongsTo { return $this->belongsTo(TicketCategory::class, 'ticket_category_id'); }
    public function guest(): BelongsTo { return $this->belongsTo(TicketGuest::class, 'ticket_guest_id'); }
    public function checkedInBy(): BelongsTo { return $this->belongsTo(User::class, 'checked_in_by'); }
    public function issuer(): BelongsTo { return $this->belongsTo(User::class, 'issued_by'); }
}
