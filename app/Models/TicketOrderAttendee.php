<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketOrderAttendee extends Model
{
    protected $fillable = ['ticket_order_id','ticket_category_id','ticket_id','full_name','email','phone','unit_price_kobo'];
    protected $casts = ['unit_price_kobo'=>'integer'];
    public function order(): BelongsTo { return $this->belongsTo(TicketOrder::class, 'ticket_order_id'); }
    public function category(): BelongsTo { return $this->belongsTo(TicketCategory::class, 'ticket_category_id'); }
    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
}
