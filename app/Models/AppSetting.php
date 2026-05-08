<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'app_name',
        'default_from_name',
        'default_from_email',
        'mail_rate_per_minute',
        'timezone',
        'unsubscribe_logo_url',
        'unsubscribe_message',
    ];
}
