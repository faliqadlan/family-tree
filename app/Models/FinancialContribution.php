<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialContribution extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id', 'contributor_id', 'amount', 'currency',
        'payment_method', 'status', 'reference_number', 'note',
        'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
