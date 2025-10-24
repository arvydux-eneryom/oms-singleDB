<?php

namespace App\Http\Controllers;

use App\DTOs\IncomingSmsData;
use App\DTOs\OutgoingSmsStatusData;
use App\Http\Requests\TwilioIncomingSmsRequest;
use App\Http\Requests\TwilioStatusCallbackRequest;
use App\Services\SmsManagerService;
use App\Services\TwilioSmsService;
use Illuminate\Http\Response;

class TwilioWebhookController extends Controller
{
    public function __construct(
        protected TwilioSmsService $twilioSmsService,
        protected SmsManagerService $smsManagerService
    ) {}

    public function handleIncomingSms(TwilioIncomingSmsRequest $request): Response
    {
        // Create DTO from validated data
        $incomingSms = IncomingSmsData::fromArray($request->validated());

        // First, let TwilioSmsService save the incoming SMS
        $this->twilioSmsService->handleIncomingSms($incomingSms);

        // Then, check if this is an answer to a sent question
        $this->smsManagerService->hasAnsweredSentQuestion($incomingSms);

        return response('', 200);
    }

    public function handleOutgoingSmsStatus(TwilioStatusCallbackRequest $request): Response
    {
        // Create DTO from validated data
        $statusData = OutgoingSmsStatusData::fromArray($request->validated());

        return $this->twilioSmsService->handleOutgoingSmsStatus($statusData);
    }
}
