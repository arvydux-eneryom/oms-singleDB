<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends Model
{
    use HasFactory;
    protected $table = 'sms_messages';

    protected $fillable = [
        'sms_sid',
        'status',
        'to',
        'from',
        'body',
        'message_sid',
        'account_sid',
        'user_id',
        'message_type', // 'incoming' or 'outgoing'
    ];

    public function userSms(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
