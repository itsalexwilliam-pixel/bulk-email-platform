<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public const WARMUP_SCHEDULE = [
        1 => 10,
        2 => 20,
        3 => 40,
        4 => 50,
        5 => 60,
        6 => 70,
        7 => 80,
    ];

    protected $fillable = [
        'name',
        'subject',
        'body',
        'status',
        'scheduled_at',
        'attachment_path',
        'attachment_name',
        'warmup_enabled',
        'warmup_day',
        'warmup_started_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'warmup_enabled' => 'boolean',
        'warmup_started_at' => 'datetime',
    ];

    public function contacts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'campaign_contact');
    }

    public function emailQueue(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailQueue::class);
    }

    public function getEffectiveWarmupDay(): int
    {
        if (empty($this->warmup_started_at)) {
            return max(1, min(7, (int) ($this->warmup_day ?: 1)));
        }

        $elapsedDays = $this->warmup_started_at->startOfDay()->diffInDays(now()->startOfDay());
        $calculatedDay = 1 + $elapsedDays;

        return max(1, min(7, $calculatedDay));
    }

    public function syncWarmupProgress(): void
    {
        $effectiveDay = $this->getEffectiveWarmupDay();

        if ((int) $this->warmup_day !== $effectiveDay) {
            $this->warmup_day = $effectiveDay;
            $this->save();
        }
    }

    public function currentWarmupCap(): int
    {
        $day = $this->getEffectiveWarmupDay();

        return self::WARMUP_SCHEDULE[$day] ?? self::WARMUP_SCHEDULE[7];
    }
}
