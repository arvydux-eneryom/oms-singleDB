<?php

namespace App\Contracts\Repositories;

use App\Models\SmsResponse;

interface SmsResponseRepositoryInterface
{
    /**
     * Create a new SMS response record.
     */
    public function create(string $from, int $sentQuestionId, string $answer): SmsResponse;
}
