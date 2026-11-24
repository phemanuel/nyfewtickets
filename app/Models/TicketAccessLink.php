<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAccessLink extends Model
{
    protected $fillable = ['ticket_order_id','token_hash','expires_at','used_at'];
    protected $casts = ['expires_at'=>'datetime', 'used_at'=>'datetime'];
    public function order(): BelongsTo { return $this->belongsTo(TicketOrder::class, 'ticket_order_id'); }
}
