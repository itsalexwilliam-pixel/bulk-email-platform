<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unsubscribe extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'contact_id',
        'unsubscribed_at',
        'created_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
