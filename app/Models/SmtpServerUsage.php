<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmtpServerUsage extends Model
{
    protected $fillable = [
        'smtp_server_id',
        'account_id',
        'usage_date',
        'sent_count',
        'fail_count',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'sent_count' => 'integer',
        'fail_count' => 'integer',
    ];

    public function smtpServer(): BelongsTo
    {
        return $this->belongsTo(SmtpServer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
