<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripEnrollment extends Model
{
    protected $fillable = [
        'drip_campaign_id',
        'contact_id',
        'account_id',
        'current_step',
        'next_send_at',
        'status',
    ];

    protected $casts = [
        'next_send_at' => 'datetime',
        'current_step' => 'integer',
    ];

    public function dripCampaign()
    {
        return $this->belongsTo(DripCampaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
