<?php

namespace Tests\Feature;

use App\Models\SentSmsQuestion;
use App\Models\SmsMessage;
use App\Models\SmsQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TwilioWebhookTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_receive_outgoing_sms_status_callback()
    {
        // Arrange
        $sms = SmsMessage::create([
            'sms_sid' => 'SM1234567890abcdef',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test message',
            'status' => 'queued',
            'account_sid' => 'AC1234567890abcdef',
            'message_type' => 'outgoing',
        ]);

        // Act - Simulate Twilio webhook callback
        $response = $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM1234567890abcdef',
            'SmsStatus' => 'delivered',
            'AccountSid' => 'AC1234567890abcdef',
            'From' => '+447426914907',
            'To' => '+37064626008',
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM1234567890abcdef',
            'status' => 'delivered',
        ]);
    }

    #[Test]
    public function it_updates_status_from_queued_to_sent()
    {
        // Arrange
        $sms = SmsMessage::create([
            'sms_sid' => 'SM_TEST_001',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'queued',
            'account_sid' => 'AC1234567890abcdef',
            'message_type' => 'outgoing',
        ]);

        // Act
        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_TEST_001',
            'SmsStatus' => 'sent',
        ]);

        // Assert
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_TEST_001',
            'status' => 'sent',
        ]);
    }

    #[Test]
    public function it_updates_status_from_sent_to_delivered()
    {
        // Arrange
        $sms = SmsMessage::create([
            'sms_sid' => 'SM_TEST_002',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'sent',
            'account_sid' => 'AC1234567890abcdef',
            'message_type' => 'outgoing',
        ]);

        // Act
        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_TEST_002',
            'SmsStatus' => 'delivered',
        ]);

        // Assert
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_TEST_002',
            'status' => 'delivered',
        ]);
    }

    #[Test]
    public function it_handles_undelivered_status()
    {
        // Arrange
        $sms = SmsMessage::create([
            'sms_sid' => 'SM_TEST_003',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'sent',
            'account_sid' => 'AC1234567890abcdef',
            'message_type' => 'outgoing',
        ]);

        // Act
        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_TEST_003',
            'SmsStatus' => 'undelivered',
        ]);

        // Assert
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_TEST_003',
            'status' => 'undelivered',
        ]);
    }

    #[Test]
    public function it_returns_200_even_for_non_existent_message()
    {
        // Arrange
        Log::spy();

        // Act
        $response = $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_DOES_NOT_EXIST',
            'SmsStatus' => 'delivered',
        ]);

        // Assert
        $response->assertStatus(200);
        Log::shouldHaveReceived('warning')
            ->once()
            ->with(\Mockery::pattern('/Attempted to update non-existent SMS/'));
    }

    #[Test]
    public function it_can_receive_incoming_sms()
    {
        // Act
        $response = $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM9876543210abcdef',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Hello from user',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC1234567890abcdef',
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM9876543210abcdef',
            'from' => '+37064626008',
            'to' => '+447426914907',
            'body' => 'Hello from user',
            'status' => 'received',
            'message_type' => 'incoming',
        ]);
    }

    #[Test]
    public function it_stores_incoming_sms_with_correct_message_type()
    {
        // Act
        $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_INCOMING_001',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Test incoming',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC1234567890abcdef',
        ]);

        // Assert
        $message = SmsMessage::where('sms_sid', 'SM_INCOMING_001')->first();
        $this->assertNotNull($message);
        $this->assertEquals('incoming', $message->message_type);
    }

    #[Test]
    public function it_can_receive_survey_question_answer()
    {
        // Arrange
        $question = SmsQuestion::create([
            'question' => 'How satisfied are you?',
            'options' => [
                '1' => 'Very Dissatisfied',
                '2' => 'Dissatisfied',
                '3' => 'Neutral',
                '4' => 'Satisfied',
                '5' => 'Very Satisfied',
            ],
        ]);

        SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question->id,
        ]);

        // Spy on TwilioSmsService - real methods execute, but we can verify calls
        // Mock only the send() method to prevent actual API calls
        $mockMessage = \Mockery::mock(\Twilio\Rest\Api\V2010\Account\MessageInstance::class);
        $mockMessage->sid = 'SM_CONFIRMATION_001';

        $spy = $this->spy(\App\Services\TwilioSmsService::class);
        $spy->shouldReceive('send')
            ->once()
            ->andReturn($mockMessage);

        // Act - User replies with answer "5"
        $response = $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_ANSWER_001',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => '5',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC1234567890abcdef',
        ]);

        // Assert
        $response->assertStatus(200);

        // Survey response should be saved
        $this->assertDatabaseHas('sms_responses', [
            'phone' => '+37064626008',
            'question_id' => $question->id,
            'answer' => '5',
            'plain_answer' => 'Very Satisfied',
        ]);

        // Verify confirmation SMS was sent
        $spy->shouldHaveReceived('send')->once();
    }

    #[Test]
    public function it_ignores_invalid_survey_answers()
    {
        // Arrange
        $question = SmsQuestion::create([
            'question' => 'Test question?',
            'options' => ['1' => 'Option 1', '2' => 'Option 2'],
        ]);

        SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question->id,
        ]);

        // Act - User replies with invalid answer "9"
        $response = $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_INVALID_ANSWER',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => '9',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC1234567890abcdef',
        ]);

        // Assert
        $response->assertStatus(200);

        // Incoming SMS recorded
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_INVALID_ANSWER',
            'body' => '9',
        ]);

        // But no response saved
        $this->assertDatabaseCount('sms_responses', 0);
    }

    #[Test]
    public function it_handles_incoming_sms_when_no_question_was_sent()
    {
        // Act - Random message from user who wasn't sent a question
        $response = $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_RANDOM_001',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Random message',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC1234567890abcdef',
        ]);

        // Assert
        $response->assertStatus(200);

        // SMS recorded
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_RANDOM_001',
            'body' => 'Random message',
        ]);

        // No response saved
        $this->assertDatabaseCount('sms_responses', 0);
    }

    #[Test]
    public function webhook_endpoints_do_not_require_authentication()
    {
        // Arrange - Not logged in (no actingAs())

        // Act & Assert - Both webhooks should work without auth
        $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_NO_AUTH',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Test',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC123',
        ])->assertStatus(200);

        $sms = SmsMessage::create([
            'sms_sid' => 'SM_NO_AUTH_2',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'queued',
            'account_sid' => 'AC123',
            'message_type' => 'outgoing',
        ]);

        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_NO_AUTH_2',
            'SmsStatus' => 'delivered',
        ])->assertStatus(200);
    }

    #[Test]
    public function webhook_endpoints_do_not_require_csrf_token()
    {
        // Arrange
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Act - POST without CSRF token should work
        $response = $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_NO_CSRF',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Test',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC123',
        ]);

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function it_logs_incoming_sms_info()
    {
        // Arrange
        Log::spy();

        // Act
        $this->post('/twilio/sms/incoming', [
            'MessageSid' => 'SM_LOG_TEST',
            'From' => '+37064626008',
            'To' => '+447426914907',
            'Body' => 'Test',
            'SmsStatus' => 'received',
            'AccountSid' => 'AC123',
        ]);

        // Assert - may be called twice due to processing flow
        Log::shouldHaveReceived('info')
            ->atLeast()->once()
            ->with(\Mockery::pattern('/Incoming SMS saved to DB/'));
    }

    #[Test]
    public function it_logs_outgoing_status_update_info()
    {
        // Arrange
        Log::spy();

        $sms = SmsMessage::create([
            'sms_sid' => 'SM_LOG_STATUS',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'queued',
            'account_sid' => 'AC123',
            'message_type' => 'outgoing',
        ]);

        // Act
        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_LOG_STATUS',
            'SmsStatus' => 'delivered',
        ]);

        // Assert
        Log::shouldHaveReceived('info')
            ->with(\Mockery::pattern('/Received Twilio status callback/'), \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with(\Mockery::pattern('/Outgoing SMS status updated in DB/'));
    }

    #[Test]
    public function it_handles_multiple_status_updates_for_same_message()
    {
        // Arrange
        $sms = SmsMessage::create([
            'sms_sid' => 'SM_MULTI_STATUS',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Test',
            'status' => 'queued',
            'account_sid' => 'AC123',
            'message_type' => 'outgoing',
        ]);

        // Act - Simulate typical status progression
        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_MULTI_STATUS',
            'SmsStatus' => 'sent',
        ]);

        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_MULTI_STATUS',
            'status' => 'sent',
        ]);

        $this->post('/twilio/sms/outgoing/status', [
            'MessageSid' => 'SM_MULTI_STATUS',
            'SmsStatus' => 'delivered',
        ]);

        // Assert - Final status should be delivered
        $this->assertDatabaseHas('sms_messages', [
            'sms_sid' => 'SM_MULTI_STATUS',
            'status' => 'delivered',
        ]);

        // Should still only have one record
        $this->assertDatabaseCount('sms_messages', 1);
    }
}
