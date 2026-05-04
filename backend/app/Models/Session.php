<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Session Model
 * 
 * Represents user sessions for tracking login activity
 */
class Session extends Model
{
    protected $table = 'sessions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'device',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
