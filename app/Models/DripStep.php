<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripStep extends Model
{
    protected $fillable = [
        'drip_campaign_id',
        'position',
        'subject',
        'body',
        'delay_days',
    ];

    protected $casts = [
        'delay_days' => 'integer',
        'position'   => 'integer',
    ];

    public function dripCampaign()
    {
        return $this->belongsTo(DripCampaign::class);
    }
}
