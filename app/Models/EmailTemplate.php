<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'subject',
        'body',
        'category',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }
}
