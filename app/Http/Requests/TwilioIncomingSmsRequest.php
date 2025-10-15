<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwilioIncomingSmsRequest extends FormRequest
{
    /**
     * Webhooks don't require user authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for Twilio incoming SMS webhook
     */
    public function rules(): array
    {
        return [
            'MessageSid' => ['required', 'string', 'starts_with:SM'],
            'From' => ['required', 'string'],
            'To' => ['required', 'string'],
            'Body' => ['required', 'string', 'max:1600'],
            'SmsStatus' => ['nullable', 'string', 'in:received,sending,sent,failed,delivered,undelivered,queued'],
            'AccountSid' => ['required', 'string', 'starts_with:AC'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'MessageSid.required' => 'Invalid webhook: Missing MessageSid',
            'MessageSid.starts_with' => 'Invalid webhook: Invalid MessageSid format',
            'From.required' => 'Invalid webhook: Missing From',
            'To.required' => 'Invalid webhook: Missing To',
            'Body.required' => 'Invalid webhook: Missing Body',
            'AccountSid.required' => 'Invalid webhook: Missing AccountSid',
            'AccountSid.starts_with' => 'Invalid webhook: Invalid AccountSid format',
        ];
    }
}
