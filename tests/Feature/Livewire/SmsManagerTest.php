<?php

namespace Tests\Feature\Livewire;

use App\Jobs\SendBulkSmsJob;
use App\Livewire\Integrations\SmsManager;
use App\Models\SmsMessage;
use App\Models\SmsQuestion;
use App\Models\User;
use App\Services\SmsManagerService;
use App\Services\TwilioSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

class SmsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_mount_and_load_sms_messages()
    {
        // Arrange
        SmsMessage::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $component = Livewire::test(SmsManager::class);

        // Assert
        $this->assertCount(5, $component->smsMessages);
    }

    #[Test]
    public function it_loads_account_balance_on_mount()
    {
        // Arrange
        $mockBalance = Mockery::mock();
        $mockBalance->balance = '15.52';
        $mockBalance->currency = 'USD';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockBalance) {
            $mockClient = Mockery::mock();
            $mockClient->balance = Mockery::mock();
            $mockClient->balance->shouldReceive('fetch')->andReturn($mockBalance);

            $reflection = new \ReflectionClass($mock);
            $property = $reflection->getProperty('client');
            $property->setAccessible(true);
            $property->setValue($mock, $mockClient);
        });

        // Act
        $component = Livewire::test(SmsManager::class);

        // Assert
        $this->assertEquals('15.52 USD', $component->accountBalance);
    }

    #[Test]
    public function it_can_send_single_sms_successfully()
    {
        // Arrange
        Log::spy();

        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';
        $mockMessage->status = 'queued';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockMessage) {
            $mock->shouldReceive('send')
                ->once()
                ->with('+37064626008', 'Test message', $this->user->id)
                ->andReturn($mockMessage);
        });

        // Act
        Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', 'Test message')
            ->call('sendSingleSms')
            ->assertSet('successMessage', 'SMS sent successfully!')
            ->assertSet('errorMessage', null)
            ->assertSet('to', null)
            ->assertSet('body', null);

        // Assert
        Log::shouldHaveReceived('info')
            ->once()
            ->with('SMS sent successfully', Mockery::type('array'));
    }

    #[Test]
    public function it_validates_phone_number_format_for_single_sms()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('to', 'invalid-phone')
            ->set('body', 'Test')
            ->call('sendSingleSms')
            ->assertHasErrors(['to']);
    }

    #[Test]
    public function it_validates_body_is_required_for_single_sms()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', '')
            ->call('sendSingleSms')
            ->assertHasErrors(['body']);
    }

    #[Test]
    public function it_validates_body_max_length_for_single_sms()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', str_repeat('a', 161))
            ->call('sendSingleSms')
            ->assertHasErrors(['body']);
    }

    #[Test]
    public function it_displays_error_message_when_sms_send_fails()
    {
        // Arrange
        Log::spy();

        $this->mock(TwilioSmsService::class, function ($mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new \RuntimeException('Failed to send SMS: Invalid phone number format'));
        });

        // Act
        Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', 'Test')
            ->call('sendSingleSms')
            ->assertSet('errorMessage', 'Failed to send SMS: Invalid phone number format')
            ->assertSet('successMessage', null);

        // Assert
        Log::shouldHaveReceived('error')
            ->once()
            ->with('SMS sending failed', Mockery::type('array'));
    }

    #[Test]
    public function it_can_send_bulk_sms()
    {
        // Arrange
        Bus::fake();

        // Act
        Livewire::test(SmsManager::class)
            ->set('selectedUsers', ['+37064626008', '+37065670928'])
            ->set('bodyForBulkSms', 'Bulk message')
            ->call('sendBulkSms');

        // Assert
        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 2
                && $batch->jobs->every(fn($job) => $job instanceof SendBulkSmsJob);
        });
    }

    #[Test]
    public function it_validates_bulk_sms_body_is_required()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('selectedUsers', ['+37064626008'])
            ->set('bodyForBulkSms', '')
            ->call('sendBulkSms')
            ->assertHasErrors(['bodyForBulkSms']);
    }

    #[Test]
    public function it_can_send_single_sms_from_bulk_list()
    {
        // Arrange
        Log::spy();

        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockMessage) {
            $mock->shouldReceive('send')
                ->once()
                ->with('+37064626008', 'Bulk message', $this->user->id)
                ->andReturn($mockMessage);
        });

        // Act
        Livewire::test(SmsManager::class)
            ->set('bodyForBulkSms', 'Bulk message')
            ->call('sendSingleSmsFromBulk', '+37064626008')
            ->assertSet('successMessage', 'SMS sent successfully!');
    }

    #[Test]
    public function it_validates_body_for_single_sms_from_bulk()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('bodyForBulkSms', '')
            ->call('sendSingleSmsFromBulk', '+37064626008')
            ->assertHasErrors(['bodyForBulkSms']);
    }

    #[Test]
    public function it_can_send_question_sms()
    {
        // Arrange
        Log::spy();

        SmsQuestion::create([
            'question' => 'Test question?',
            'options' => ['1' => 'Option 1', '2' => 'Option 2'],
        ]);

        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockMessage) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturn($mockMessage);
        });

        // Act
        Livewire::test(SmsManager::class)
            ->set('questionTo', '+37064626008')
            ->call('sendQuestion')
            ->assertSet('successMessage', 'Question SMS sent successfully!')
            ->assertSet('errorMessage', null)
            ->assertSet('questionTo', '');

        // Assert
        Log::shouldHaveReceived('info')
            ->once()
            ->with('Question SMS sent successfully', Mockery::type('array'));
    }

    #[Test]
    public function it_validates_question_phone_number_format()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('questionTo', 'invalid')
            ->call('sendQuestion')
            ->assertHasErrors(['questionTo']);
    }

    #[Test]
    public function it_displays_error_when_question_send_fails()
    {
        // Arrange
        Log::spy();

        $this->mock(SmsManagerService::class, function ($mock) {
            $mock->shouldReceive('sendQuestion')
                ->once()
                ->andThrow(new \Exception('No questions available'));
        });

        // Act
        Livewire::test(SmsManager::class)
            ->set('questionTo', '+37064626008')
            ->call('sendQuestion')
            ->assertSet('errorMessage', 'No questions available')
            ->assertSet('successMessage', null);

        // Assert
        Log::shouldHaveReceived('error')
            ->once()
            ->with('Question SMS sending failed', Mockery::type('array'));
    }

    #[Test]
    public function it_can_refresh_balance()
    {
        // Arrange
        $mockBalance = Mockery::mock();
        $mockBalance->balance = '20.00';
        $mockBalance->currency = 'USD';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockBalance) {
            $mock->shouldReceive('getAccountBalance')
                ->once()
                ->andReturn([
                    'balance' => '20.00',
                    'currency' => 'USD',
                    'formatted' => '20.00 USD',
                ]);
        });

        // Act
        Livewire::test(SmsManager::class)
            ->call('refreshBalance')
            ->assertSet('accountBalance', '20.00 USD')
            ->assertSet('successMessage', 'Balance refreshed!');
    }

    #[Test]
    public function it_clears_balance_cache_when_refreshing()
    {
        // Arrange
        Cache::put('twilio_account_balance', ['formatted' => 'Old Balance'], 300);

        $this->mock(TwilioSmsService::class, function ($mock) {
            $mock->shouldReceive('getAccountBalance')
                ->once()
                ->andReturn([
                    'balance' => '30.00',
                    'currency' => 'USD',
                    'formatted' => '30.00 USD',
                ]);
        });

        // Act
        Livewire::test(SmsManager::class)
            ->call('refreshBalance');

        // Assert
        $this->assertFalse(Cache::has('twilio_account_balance'));
    }

    #[Test]
    public function it_can_clear_messages()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('successMessage', 'Success')
            ->set('errorMessage', 'Error')
            ->set('messageTimestamp', '12345')
            ->call('clearMessages')
            ->assertSet('successMessage', null)
            ->assertSet('errorMessage', null)
            ->assertSet('messageTimestamp', null);
    }

    #[Test]
    public function it_calculates_characters_left_correctly()
    {
        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('body', 'Hello')
            ->assertSet('charactersLeft', 155);

        Livewire::test(SmsManager::class)
            ->set('body', str_repeat('a', 160))
            ->assertSet('charactersLeft', 0);
    }

    #[Test]
    public function it_loads_only_current_user_sms_messages()
    {
        // Arrange
        $otherUser = User::factory()->create();

        // Create messages for current user
        SmsMessage::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Create messages for other user
        SmsMessage::factory()->count(5)->create([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $component = Livewire::test(SmsManager::class);

        // Assert - should only see own messages
        $this->assertCount(3, $component->smsMessages);
        $this->assertTrue(
            collect($component->smsMessages)->every(fn($msg) => $msg['user_id'] === $this->user->id)
        );
    }

    #[Test]
    public function it_loads_latest_sms_messages_first()
    {
        // Arrange
        $oldMessage = SmsMessage::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(5),
            'body' => 'Old message',
        ]);

        $newMessage = SmsMessage::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
            'body' => 'New message',
        ]);

        // Act
        $component = Livewire::test(SmsManager::class);

        // Assert
        $this->assertEquals('New message', $component->smsMessages[0]['body']);
        $this->assertEquals('Old message', $component->smsMessages[1]['body']);
    }

    #[Test]
    public function it_limits_sms_messages_to_10()
    {
        // Arrange
        SmsMessage::factory()->count(15)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $component = Livewire::test(SmsManager::class);

        // Assert
        $this->assertCount(10, $component->smsMessages);
    }

    #[Test]
    public function it_resets_form_fields_after_successful_send()
    {
        // Arrange
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockMessage) {
            $mock->shouldReceive('send')->andReturn($mockMessage);
        });

        // Act & Assert
        Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', 'Test message')
            ->call('sendSingleSms')
            ->assertSet('to', null)
            ->assertSet('body', null);
    }

    #[Test]
    public function it_sets_message_timestamp_when_showing_messages()
    {
        // Arrange
        $mockMessage = Mockery::mock(MessageInstance::class);
        $mockMessage->sid = 'SM1234567890abcdef';

        $this->mock(TwilioSmsService::class, function ($mock) use ($mockMessage) {
            $mock->shouldReceive('send')->andReturn($mockMessage);
        });

        // Act
        $component = Livewire::test(SmsManager::class)
            ->set('to', '+37064626008')
            ->set('body', 'Test')
            ->call('sendSingleSms');

        // Assert
        $this->assertNotNull($component->messageTimestamp);
        $this->assertIsString($component->messageTimestamp);
    }
}
