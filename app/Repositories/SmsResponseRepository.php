<?php

namespace App\Repositories;

use App\Models\SentSmsQuestion;
use App\Models\SmsQuestion;
use App\Models\SmsResponse;

class SmsResponseRepository
{
    public function create(SmsQuestion $question, SentSmsQuestion $sentSmsQuestion, string $phone, string $answer): SmsResponse
    {
        return SmsResponse::create([
            'question_id' => $question->id,
            'sent_sms_question_id' => $sentSmsQuestion->id,
            'phone' => $phone,
            'answer' => $answer,
            'plain_answer' => $question->options[$answer],
        ]);
    }

    public function hasAnswered(int $sentSmsQuestionId): bool
    {
        return SmsResponse::where('sent_sms_question_id', $sentSmsQuestionId)
            ->exists();
    }
}
