<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsResponse extends Model
{
    protected $fillable = [
        'question_id',
        'sent_sms_question_id',
        'phone',
        'answer',
        'plain_answer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(SmsQuestion::class, 'question_id');
    }

    public function sentSmsQuestion(): BelongsTo
    {
        return $this->belongsTo(SentSmsQuestion::class, 'sent_sms_question_id');
    }
}
