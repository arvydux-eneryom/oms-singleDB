<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwilioStatusCallbackRequest extends FormRequest
{
    /**
     * Webhooks don't require user authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for Twilio status callback webhook
     */
    public function rules(): array
    {
        return [
            'MessageSid' => ['required', 'string', 'starts_with:SM'],
            'SmsStatus' => ['required', 'string', 'in:queued,sending,sent,failed,delivered,undelivered'],
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
            'SmsStatus.required' => 'Invalid webhook: Missing SmsStatus',
            'SmsStatus.in' => 'Invalid webhook: Invalid SmsStatus value',
        ];
    }
}
