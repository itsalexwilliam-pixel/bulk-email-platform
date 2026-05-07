<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountUsage extends Model
{
    protected $fillable = [
        'account_id',
        'usage_date',
        'emails_sent_count',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
