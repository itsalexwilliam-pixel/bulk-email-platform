<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'emails_per_day',
        'campaigns_limit',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
