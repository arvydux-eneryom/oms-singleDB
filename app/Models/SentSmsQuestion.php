<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentSmsQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'sms_question_id',
        'user_id',
    ];

    public function smsQuestion()
    {
        return $this->belongsTo(SmsQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
