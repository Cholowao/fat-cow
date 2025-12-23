<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'category',
        'transaction_date',
        'sort_order',
    ];

    // Cast attributes to proper types
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // Relationship to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope to filter by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Scope for credits (money received)
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    // Scope for debits (expenses)
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }
}
