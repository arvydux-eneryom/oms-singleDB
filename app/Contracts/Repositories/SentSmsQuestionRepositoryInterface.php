<?php

namespace App\Contracts\Repositories;

use App\Models\SentSmsQuestion;

interface SentSmsQuestionRepositoryInterface
{
    /**
     * Create a new sent SMS question record.
     */
    public function create(string $to, int $questionId, ?int $userId = null): SentSmsQuestion;

    /**
     * Get the latest sent question for a phone number.
     */
    public function getLatestForPhone(string $phone): ?SentSmsQuestion;
}
