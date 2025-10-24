<?php

namespace App\Services;

use App\DTOs\IncomingSmsData;
use App\Models\SentSmsQuestion;
use App\Models\SmsQuestion;
use App\Repositories\SentSmsQuestionRepository;
use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsResponseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsManagerService
{
    private string $firstTimeWelcomeMessage = 'Welcome to our service!';

    private string $returningUserWelcomeMessage = 'Welcome back!';

    public function __construct(
        protected SmsServiceInterface $smsService,
        protected SmsMessageRepository $smsMessageRepository,
        protected SmsResponseRepository $smsResponseRepository,
        protected SentSmsQuestionRepository $sentSmsQuestionRepository
    ) {}

    public function sendQuestion(string $to, ?int $userId = null): void
    {
        if (SmsQuestion::count() === 0) {
            Log::warning("No SMS questions found in DB. Cannot send question to {$to}.");

            return;
        }

        DB::transaction(function () use ($to, $userId) {
            $questionMessage = SmsQuestion::inRandomOrder()->first(); // random question

            $options = collect($questionMessage->options)
                ->map(function ($value, $key) {
                    return "{$key}. {$value}";
                })
                ->implode("\n");

            $message = $questionMessage->question."\n".
                $options;

            $message = $this->makeCorrespondingWelcomeMessage($to)."\n\n".$message;
            $this->smsService->send($to, $message, $userId);

            $this->sentSmsQuestionRepository->create($to, $questionMessage->id, $userId);

            Log::info("Outgoing SMS to:{$to} with question id: {$questionMessage->id} was saved to DB.");
        });
    }

    // Returns true if a valid answer was saved, false otherwise
    public function hasAnsweredSentQuestion(IncomingSmsData $data): bool
    {
        $from = $data->from;
        $sentSmsQuestion = $this->sentSmsQuestionRepository->getLatestForPhone($from);

        if (! $sentSmsQuestion) {
            Log::info("No sent question found for phone: {$from}");

            return false;
        }

        $smsQuestion = $sentSmsQuestion->smsQuestion;
        $answer = trim($data->body);

        Log::info('Processing SMS answer', [
            'from' => $from,
            'answer' => $answer,
            'question_id' => $smsQuestion->id,
            'valid_options' => array_keys($smsQuestion->options),
        ]);

        if (! isset($smsQuestion->options[$answer])) {
            Log::info("Invalid answer '{$answer}' for question {$smsQuestion->id}. Valid options: ".implode(', ', array_keys($smsQuestion->options)));

            return false;
        }

        // Prevent duplicate responses to the same sent question instance
        if ($this->smsResponseRepository->hasAnswered($sentSmsQuestion->id)) {
            Log::info("Duplicate answer detected for sent question {$sentSmsQuestion->id} from {$from}");

            return false;
        }

        $this->saveSmsResponseToDb($smsQuestion, $sentSmsQuestion, $from, $answer);

        return true;
    }

    protected function hasSentSmsToUserBefore(string $phone): bool
    {
        return $this->smsMessageRepository->hasOutgoingMessagesTo($phone);
    }

    public function makeCorrespondingWelcomeMessage(string $phone): string
    {
        return $this->hasSentSmsToUserBefore($phone)
            ? $this->returningUserWelcomeMessage
            : $this->firstTimeWelcomeMessage;
    }

    protected function saveSmsResponseToDb(SmsQuestion $smsQuestion, SentSmsQuestion $sentSmsQuestion, string $from, string $answer): void
    {
        DB::transaction(function () use ($smsQuestion, $sentSmsQuestion, $from, $answer) {
            $this->smsResponseRepository->create($smsQuestion, $sentSmsQuestion, $from, $answer);
            Log::info("Incoming SMS from:{$from} with answer: {$answer} to question id: {$smsQuestion->id} was saved to DB.");

            $this->answerToResponse($from, 'You selected: '.$smsQuestion->options[$answer], $sentSmsQuestion->user_id);
        });
    }

    protected function answerToResponse(string $to, string $answer, ?int $userId = null): void
    {
        $this->smsService->send($to, $answer, $userId);
        Log::info("Outgoing SMS to:{$to}  to response with answer: {$answer} was sent and saved to DB.");
    }
}
