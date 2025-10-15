<?php

namespace App\Repositories;

use App\Models\SentSmsQuestion;

class SentSmsQuestionRepository
{
    public function create(string $to, int $smsQuestionId, ?int $userId = null): SentSmsQuestion
    {
        return SentSmsQuestion::create([
            'to' => $to,
            'sms_question_id' => $smsQuestionId,
            'user_id' => $userId,
        ]);
    }

    public function getLatestForPhone(string $phone): ?SentSmsQuestion
    {
        return SentSmsQuestion::latest()
            ->where('to', $phone)
            ->first();
    }
}
