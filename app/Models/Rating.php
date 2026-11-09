<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $guarded = ['id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function average(): float
    {
        return round(((int) $this->quality + (int) $this->price_score + (int) $this->dealing) / 3, 1);
    }
}
