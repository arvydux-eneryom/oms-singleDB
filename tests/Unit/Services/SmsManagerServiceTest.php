<?php

namespace Tests\Unit\Services;

use App\DTOs\IncomingSmsData;
use App\Models\SentSmsQuestion;
use App\Models\SmsMessage;
use App\Models\SmsQuestion;
use App\Models\SmsResponse;
use App\Repositories\SentSmsQuestionRepository;
use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsResponseRepository;
use App\Services\SmsManagerService;
use App\Services\SmsServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

class SmsManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createService(SmsServiceInterface $smsService): SmsManagerService
    {
        return new SmsManagerService(
            smsService: $smsService,
            smsMessageRepository: new SmsMessageRepository(),
            smsResponseRepository: new SmsResponseRepository(),
            sentSmsQuestionRepository: new SentSmsQuestionRepository()
        );
    }

    #[Test]
    public function it_can_send_question_to_new_user()
    {
        // Arrange
        Log::spy();

        $question = SmsQuestion::create([
            'question' => 'How satisfied are you with our service?',
            'options' => [
                '1' => 'Very Dissatisfied',
                '2' => 'Dissatisfied',
                '3' => 'Neutral',
                '4' => 'Satisfied',
                '5' => 'Very Satisfied',
            ],
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        // Expect send to be called with welcome message and question
        $mockSmsService->shouldReceive('send')
            ->once()
            ->with(
                '+37064626008',
                Mockery::on(function ($message) {
                    return str_contains($message, 'Welcome to our service!')
                        && str_contains($message, 'How satisfied are you')
                        && str_contains($message, '1. Very Dissatisfied')
                        && str_contains($message, '5. Very Satisfied');
                })
            )
            ->andReturn($mockMessage);

        $service = $this->createService($mockSmsService);

        // Act
        $service->sendQuestion('+37064626008');

        // Assert
        $this->assertDatabaseHas('sent_sms_questions', [
            'to' => '+37064626008',
            'sms_question_id' => $question->id,
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(Mockery::pattern('/Outgoing SMS to:\+37064626008 with question id/'));
    }

    #[Test]
    public function it_sends_returning_user_welcome_message()
    {
        // Arrange
        $question = SmsQuestion::create([
            'question' => 'Test question?',
            'options' => ['1' => 'Option 1', '2' => 'Option 2'],
        ]);

        // Create existing outgoing SMS to mark user as "returning"
        SmsMessage::create([
            'sms_sid' => 'SM_OLD_MESSAGE',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Previous message',
            'status' => 'delivered',
            'account_sid' => 'AC1234567890abcdef',
            'message_type' => 'outgoing',
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $mockSmsService->shouldReceive('send')
            ->once()
            ->with(
                '+37064626008',
                Mockery::on(function ($message) {
                    return str_contains($message, 'Welcome back!');
                })
            )
            ->andReturn($mockMessage);

        $service = $this->createService($mockSmsService);

        // Act
        $service->sendQuestion('+37064626008');

        // Assert - implicitly verified by mock expectation
        $this->assertTrue(true);
    }

    #[Test]
    public function it_does_not_send_question_when_no_questions_exist()
    {
        // Arrange
        Log::spy();

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockSmsService->shouldNotReceive('send');

        $service = $this->createService($mockSmsService);

        // Act
        $service->sendQuestion('+37064626008');

        // Assert
        Log::shouldHaveReceived('warning')
            ->once()
            ->with(Mockery::pattern('/No SMS questions found in DB/'));

        $this->assertDatabaseCount('sent_sms_questions', 0);
    }

    #[Test]
    public function it_can_validate_and_save_correct_answer()
    {
        // Arrange
        Log::spy();

        $question = SmsQuestion::create([
            'question' => 'Test question?',
            'options' => [
                '1' => 'Option 1',
                '2' => 'Option 2',
                '3' => 'Option 3',
            ],
        ]);

        $sentQuestion = SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question->id,
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        // Expect confirmation SMS to be sent
        $mockSmsService->shouldReceive('send')
            ->once()
            ->with('+37064626008', 'You selected: Option 2')
            ->andReturn($mockMessage);

        $service = $this->createService($mockSmsService);

        // Create DTO with answer
        $data = new IncomingSmsData(
            messageSid: 'SM1234567890abcdef',
            from: '+37064626008',
            to: '+447426914907',
            body: '2',
            accountSid: 'AC1234567890abcdef',
            smsStatus: 'received',
        );

        // Act
        $result = $service->hasAnsweredSentQuestion($data);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('sms_responses', [
            'question_id' => $question->id,
            'phone' => '+37064626008',
            'answer' => '2',
            'plain_answer' => 'Option 2',
        ]);

        // Verify logs (2 log calls: one for saving response, one for sending confirmation)
        Log::shouldHaveReceived('info')
            ->with(Mockery::pattern('/Incoming SMS from:\+37064626008 with answer: 2/'));

        Log::shouldHaveReceived('info')
            ->with(Mockery::pattern('/Outgoing SMS to:\+37064626008  to response with answer:/'));
    }

    #[Test]
    public function it_returns_false_for_invalid_answer()
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

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockSmsService->shouldNotReceive('send');

        $service = $this->createService($mockSmsService);

        // Create DTO with invalid answer
        $data = new IncomingSmsData(
            messageSid: 'SM1234567890abcdef',
            from: '+37064626008',
            to: '+447426914907',
            body: '9', // Invalid option
            accountSid: 'AC1234567890abcdef',
            smsStatus: 'received',
        );

        // Act
        $result = $service->hasAnsweredSentQuestion($data);

        // Assert
        $this->assertFalse($result);
        $this->assertDatabaseCount('sms_responses', 0);
    }

    #[Test]
    public function it_returns_false_when_no_question_was_sent_to_user()
    {
        // Arrange
        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $service = $this->createService($mockSmsService);

        $data = new IncomingSmsData(
            messageSid: 'SM1234567890abcdef',
            from: '+37064626008',
            to: '+447426914907',
            body: '1',
            accountSid: 'AC1234567890abcdef',
            smsStatus: 'received',
        );

        // Act
        $result = $service->hasAnsweredSentQuestion($data);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_trims_answer_before_validation()
    {
        // Arrange
        $question = SmsQuestion::create([
            'question' => 'Test?',
            'options' => ['1' => 'Option 1'],
        ]);

        SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question->id,
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $mockSmsService->shouldReceive('send')
            ->once()
            ->andReturn($mockMessage);

        $service = $this->createService($mockSmsService);

        // Answer with spaces
        $data = new IncomingSmsData(
            messageSid: 'SM1234567890abcdef',
            from: '+37064626008',
            to: '+447426914907',
            body: '  1  ',
            accountSid: 'AC1234567890abcdef',
            smsStatus: 'received',
        );

        // Act
        $result = $service->hasAnsweredSentQuestion($data);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('sms_responses', [
            'answer' => '1', // Trimmed
        ]);
    }

    #[Test]
    public function it_gets_latest_sent_question_for_user()
    {
        // Arrange
        $question1 = SmsQuestion::create([
            'question' => 'Old question?',
            'options' => ['1' => 'Option 1'],
        ]);

        $question2 = SmsQuestion::create([
            'question' => 'New question?',
            'options' => ['1' => 'New Option 1'],
        ]);

        // Create old sent question
        $oldQuestion = SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question1->id,
        ]);
        $oldQuestion->created_at = now()->subHour(); // Ensure older timestamp
        $oldQuestion->save();

        // Create newer sent question
        $newQuestion = SentSmsQuestion::create([
            'to' => '+37064626008',
            'sms_question_id' => $question2->id,
        ]);
        $newQuestion->created_at = now(); // Latest timestamp
        $newQuestion->save();

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        // Should send confirmation for the LATEST question
        $mockSmsService->shouldReceive('send')
            ->once()
            ->with('+37064626008', 'You selected: New Option 1')
            ->andReturn($mockMessage);

        $service = $this->createService($mockSmsService);

        $data = new IncomingSmsData(
            messageSid: 'SM1234567890abcdef',
            from: '+37064626008',
            to: '+447426914907',
            body: '1',
            accountSid: 'AC1234567890abcdef',
            smsStatus: 'received',
        );

        // Act
        $result = $service->hasAnsweredSentQuestion($data);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('sms_responses', [
            'question_id' => $question2->id, // Latest question
            'plain_answer' => 'New Option 1',
        ]);
    }

    #[Test]
    public function it_determines_first_time_user_correctly()
    {
        // Arrange
        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $service = $this->createService($mockSmsService);

        // Act
        $message = $service->makeCorrespondingWelcomeMessage('+37064626008');

        // Assert
        $this->assertEquals('Welcome to our service!', $message);
    }

    #[Test]
    public function it_determines_returning_user_correctly()
    {
        // Arrange
        SmsMessage::create([
            'sms_sid' => 'SM_OLD',
            'to' => '+37064626008',
            'from' => '+447426914907',
            'body' => 'Previous',
            'status' => 'delivered',
            'account_sid' => 'AC123',
            'message_type' => 'outgoing',
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $service = $this->createService($mockSmsService);

        // Act
        $message = $service->makeCorrespondingWelcomeMessage('+37064626008');

        // Assert
        $this->assertEquals('Welcome back!', $message);
    }

    #[Test]
    public function it_only_considers_outgoing_messages_for_returning_user_check()
    {
        // Arrange - Only incoming message exists
        SmsMessage::create([
            'sms_sid' => 'SM_INCOMING',
            'to' => '+447426914907',
            'from' => '+37064626008',
            'body' => 'User sent us a message',
            'status' => 'received',
            'account_sid' => 'AC123',
            'message_type' => 'incoming',
        ]);

        $mockSmsService = Mockery::mock(SmsServiceInterface::class);
        $service = $this->createService($mockSmsService);

        // Act
        $message = $service->makeCorrespondingWelcomeMessage('+37064626008');

        // Assert - Should be first time (we didn't send to them before)
        $this->assertEquals('Welcome to our service!', $message);
    }
}
