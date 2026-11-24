<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketOrder extends Model
{
    protected $fillable = ['event_id','reference','buyer_name','buyer_email','buyer_phone','access_hash','amount_kobo','currency','status','expires_at','paid_at','paystack_transaction_id','payment_issue'];
    protected $casts = ['expires_at'=>'datetime', 'paid_at'=>'datetime', 'amount_kobo'=>'integer'];
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function attendees(): HasMany { return $this->hasMany(TicketOrderAttendee::class); }
}
