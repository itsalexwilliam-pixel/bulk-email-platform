<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    protected $table = 'email_queue';

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'smtp_server_id',
        'email',
        'type',
        'subject',
        'body',
        'from_email',
        'from_name',
        'attachments',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function smtpServer()
    {
        return $this->belongsTo(SmtpServer::class);
    }
}
