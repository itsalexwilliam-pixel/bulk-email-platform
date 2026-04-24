<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpServer extends Model
{
    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'is_active',
        'last_used_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = encrypt($value);
    }

    public function getPasswordAttribute($value): string
    {
        return decrypt($value);
    }
}
