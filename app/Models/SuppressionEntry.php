<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuppressionEntry extends Model
{
    protected $table = 'suppression_list';

    protected $fillable = [
        'account_id',
        'email',
        'reason',
    ];
}
