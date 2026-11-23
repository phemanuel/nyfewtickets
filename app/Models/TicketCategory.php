<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategory extends Model
{
    protected $fillable = ['event_id','name','slug','description','type','price_kobo','capacity','sales_start_at','sales_end_at','is_active','sort_order'];
    protected $casts = ['event_id'=>'integer','price_kobo'=>'integer','capacity'=>'integer','is_active'=>'boolean','sales_start_at'=>'datetime','sales_end_at'=>'datetime'];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function getPriceNairaAttribute(): string { return number_format($this->price_kobo / 100, 2, '.', ''); }
}
