<?php

namespace App\Repositories;

use App\Contracts\Repositories\SentSmsQuestionRepositoryInterface;
use App\Models\SentSmsQuestion;

class SentSmsQuestionRepository implements SentSmsQuestionRepositoryInterface
{
    public function create(string $to, int $questionId, ?int $userId = null): SentSmsQuestion
    {
        return SentSmsQuestion::create([
            'to' => $to,
            'sms_question_id' => $questionId,
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
