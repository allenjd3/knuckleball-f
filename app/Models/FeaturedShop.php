<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedShop extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at'         => 'datetime',
            'next_billing_date' => 'datetime',
            'cancelled_at'      => 'datetime',
            'amount_paid'       => 'decimal:2',
        ];
    }

    public function cardShop(): BelongsTo
    {
        return $this->belongsTo(CardShop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function prices(): array
    {
        return [
            'monthly' => ['label' => 'Monthly', 'amount' => 999,  'display' => '$9.99/mo'],
            'yearly'  => ['label' => 'Yearly',  'amount' => 9900, 'display' => '$99/yr'],
        ];
    }
}
