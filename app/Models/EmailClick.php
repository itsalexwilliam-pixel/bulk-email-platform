<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_queue_id',
        'url',
        'clicked_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function emailQueue()
    {
        return $this->belongsTo(EmailQueue::class);
    }
}
