<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id', 'full_name', 'nickname', 'gender',
        'date_of_birth', 'date_of_death', 'place_of_birth', 'bio',
        'phone', 'phone_privacy',
        'email_privacy',
        'dob_privacy',
        'address', 'address_privacy',
        'father_name', 'mother_name',
        'graph_node_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_death' => 'date',
    ];

    protected $hidden = ['phone', 'address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
