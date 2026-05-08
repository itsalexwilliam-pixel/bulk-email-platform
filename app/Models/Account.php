<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'name',
        'plan_id',
        'owner_user_id',
        'webhook_url',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(AccountUsage::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function smtpServers(): HasMany
    {
        return $this->hasMany(SmtpServer::class);
    }

    public function emailQueues(): HasMany
    {
        return $this->hasMany(EmailQueue::class);
    }

    public function canSendEmailToday(): bool
    {
        $planLimit = $this->plan?->emails_per_day ?? 0;
        if ($planLimit <= 0) {
            return false;
        }

        $todayUsage = $this->usages()
            ->whereDate('usage_date', now()->toDateString())
            ->value('emails_sent_count') ?? 0;

        return $todayUsage < $planLimit;
    }

    public function incrementDailyUsage(int $count = 1): void
    {
        $usage = $this->usages()->firstOrCreate(
            ['usage_date' => now()->toDateString()],
            ['emails_sent_count' => 0]
        );

        $usage->increment('emails_sent_count', $count);
    }

    public function campaignsCount(): int
    {
        return $this->campaigns()->count();
    }

    public function canCreateCampaign(): bool
    {
        $limit = $this->plan?->campaigns_limit ?? 0;
        if ($limit <= 0) {
            return false;
        }

        return $this->campaignsCount() < $limit;
    }
}
