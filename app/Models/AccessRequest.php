<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessRequest extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'requester_id', 'target_id', 'requested_fields',
        'status', 'requester_message', 'target_response', 'responded_at',
    ];

    protected $casts = [
        'requested_fields' => 'array',
        'responded_at'     => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
