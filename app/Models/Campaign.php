<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'status',
        'scheduled_at',
    ];

    public function contacts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'campaign_contact');
    }

    public function emailQueue(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailQueue::class);
    }
}
