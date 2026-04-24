<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOpen extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_queue_id',
        'opened_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function emailQueue()
    {
        return $this->belongsTo(EmailQueue::class);
    }
}
