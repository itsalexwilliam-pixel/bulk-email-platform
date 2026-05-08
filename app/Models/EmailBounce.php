<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailBounce extends Model
{
    protected $fillable = [
        'email',
        'bounce_type',
        'bounce_subtype',
        'diagnostic',
        'source',
        'account_id',
        'email_queue_id',
        'bounced_at',
    ];

    protected $casts = [
        'bounced_at' => 'datetime',
    ];

    public function emailQueue()
    {
        return $this->belongsTo(EmailQueue::class);
    }
}
