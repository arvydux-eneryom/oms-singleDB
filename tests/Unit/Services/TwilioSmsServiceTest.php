<?php

namespace Tests\Unit\Services;

use App\Repositories\SmsMessageRepository;
use App\Services\TwilioSmsService;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class TwilioSmsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createService(): TwilioSmsService
    {
        $mockRepository = Mockery::mock(SmsMessageRepository::class);
        $mockRepository->shouldIgnoreMissing();

        return new TwilioSmsService($mockRepository);
    }

    #[Test]
    public function it_throws_exception_for_invalid_phone_number()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('not a valid phone number'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid phone number format');

        // Act
        $service->send('invalid-phone', 'Test message');
    }

    #[Test]
    public function it_throws_exception_for_insufficient_funds()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('insufficient funds'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SMS service temporarily unavailable');

        // Act
        $service->send('+37064626008', 'Test');
    }

    #[Test]
    public function it_throws_exception_for_unverified_number()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('unverified number'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This phone number is not verified');

        // Act
        $service->send('+37064626008', 'Test');
    }

    #[Test]
    public function it_throws_exception_for_geographic_restrictions()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('Geographic permissions'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot send SMS to this country');

        // Act
        $service->send('+37064626008', 'Test');
    }

    #[Test]
    public function it_throws_exception_for_rate_limit()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('rate limit exceeded'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Too many SMS requests');

        // Act
        $service->send('+37064626008', 'Test');
    }

    #[Test]
    public function it_logs_error_when_send_fails()
    {
        // Arrange
        Log::spy();

        $mockClient = Mockery::mock(Client::class);
        $mockMessageList = Mockery::mock(MessageList::class);

        $mockClient->messages = $mockMessageList;
        $mockMessageList->shouldReceive('create')
            ->andThrow(new TwilioException('Some error', 12345));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Act & Assert
        $exceptionThrown = false;
        try {
            $service->send('+37064626008', 'Test message');
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
        }

        // Assert exception was thrown
        $this->assertTrue($exceptionThrown, 'RuntimeException should have been thrown');

        // Verify error was logged
        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                Mockery::pattern('/Twilio SMS send failed/'),
                Mockery::on(function ($context) {
                    return $context['to'] === '+37064626008'
                        && $context['body'] === 'Test message'
                        && $context['error_code'] === 12345;
                })
            );
    }

    #[Test]
    public function it_can_fetch_account_balance_successfully()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockBalance = Mockery::mock();
        $mockBalance->balance = '15.52';
        $mockBalance->currency = 'USD';

        $mockClient->balance = Mockery::mock();
        $mockClient->balance->shouldReceive('fetch')
            ->once()
            ->andReturn($mockBalance);

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Act
        $result = $service->getAccountBalance();

        // Assert
        $this->assertEquals('15.52', $result['balance']);
        $this->assertEquals('USD', $result['currency']);
        $this->assertEquals('15.52 USD', $result['formatted']);
    }

    #[Test]
    public function it_handles_balance_fetch_failure_gracefully()
    {
        // Arrange
        Log::spy();

        $mockClient = Mockery::mock(Client::class);
        $mockClient->balance = Mockery::mock();
        $mockClient->balance->shouldReceive('fetch')
            ->once()
            ->andThrow(new \Exception('Permission denied'));

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Act
        $result = $service->getAccountBalance();

        // Assert
        $this->assertNull($result['balance']);
        $this->assertNull($result['currency']);
        $this->assertEquals('Balance unavailable', $result['formatted']);
        $this->assertEquals('Permission denied', $result['error']);

        // Verify error was logged
        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                Mockery::pattern('/Failed to fetch Twilio account balance/'),
                Mockery::type('array')
            );
    }

    #[Test]
    public function it_can_fetch_account_balance_with_default_currency()
    {
        // Arrange
        $mockClient = Mockery::mock(Client::class);
        $mockBalance = Mockery::mock();
        $mockBalance->balance = '25.00';
        $mockBalance->currency = null; // Test default USD

        $mockClient->balance = Mockery::mock();
        $mockClient->balance->shouldReceive('fetch')
            ->once()
            ->andReturn($mockBalance);

        $service = $this->createService();
        $this->injectMockClient($service, $mockClient);

        // Act
        $result = $service->getAccountBalance();

        // Assert
        $this->assertEquals('25', $result['balance']); // Cast to float loses trailing zeros
        $this->assertEquals('USD', $result['currency']); // Default
        $this->assertEquals('25.00 USD', $result['formatted']); // number_format adds them back
    }

    /**
     * Helper method to inject mock Twilio client into service
     */
    protected function injectMockClient(TwilioSmsService $service, $mockClient): void
    {
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);
    }
}
