<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Contact extends Model
{
    protected $fillable = ['account_id', 'name', 'business_name', 'email', 'website', 'is_bounced'];

    protected $casts = ['is_bounced' => 'boolean'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'contact_group');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_contact');
    }

    public function emailQueue(): HasMany
    {
        return $this->hasMany(EmailQueue::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ContactTag::class);
    }
}
