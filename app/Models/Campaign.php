<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public const WARMUP_SCHEDULE = [
        1 => 10,
        2 => 20,
        3 => 30,
        4 => 40,
        5 => 50,
        6 => 60,
        7 => 70,
        8 => 80,
        9 => 90,
        10 => 100,
        11 => 110,
        12 => 120,
        13 => 130,
        14 => 140,
        15 => 150,
        16 => 160,
        17 => 170,
        18 => 180,
        19 => 190,
        20 => 200,
        21 => 210,
    ];

    protected $fillable = [
        'account_id',
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
        'emails_per_minute',
        'ab_enabled',
        'ab_subject_b',
        'ab_body_b',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'warmup_enabled' => 'boolean',
        'warmup_started_at' => 'datetime',
        'emails_per_minute' => 'integer',
        'ab_enabled' => 'boolean',
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
            return max(1, min(21, (int) ($this->warmup_day ?: 1)));
        }

        $elapsedDays = $this->warmup_started_at->startOfDay()->diffInDays(now()->startOfDay());
        $calculatedDay = 1 + $elapsedDays;

        return max(1, min(21, $calculatedDay));
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

        return self::WARMUP_SCHEDULE[$day] ?? self::WARMUP_SCHEDULE[21];
    }
}
