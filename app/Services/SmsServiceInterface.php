<?php

namespace App\Services;

use App\DTOs\IncomingSmsData;
use App\DTOs\OutgoingSmsStatusData;
use Illuminate\Http\Response;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

interface SmsServiceInterface
{
    public function send($to, $body, ?int $userId = null): MessageInstance;

    public function sendQueued(string $to, string $body, ?int $userId = null): void;

    public function handleIncomingSms(IncomingSmsData $data): Response;

    public function handleOutgoingSmsStatus(OutgoingSmsStatusData $data): Response;
}
