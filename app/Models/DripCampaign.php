<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripCampaign extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'status',
        'group_id',
        'description',
    ];

    public function steps()
    {
        return $this->hasMany(DripStep::class)->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(DripEnrollment::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
