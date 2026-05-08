<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactTag extends Model
{
    protected $fillable = ['contact_id', 'account_id', 'tag'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
