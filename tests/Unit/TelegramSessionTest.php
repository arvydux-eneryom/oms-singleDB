<?php

namespace Tests\Unit;

use App\Models\TelegramSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TelegramSessionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_secure_random_identifier()
    {
        $identifier = TelegramSession::generateSecureIdentifier();

        $this->assertEquals(64, strlen($identifier));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $identifier);
    }

    #[Test]
    public function it_generates_unique_identifiers()
    {
        $identifier1 = TelegramSession::generateSecureIdentifier();
        $identifier2 = TelegramSession::generateSecureIdentifier();

        $this->assertNotEquals($identifier1, $identifier2);
    }

    #[Test]
    public function it_validates_active_session_correctly()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertTrue($session->isValid());
    }

    #[Test]
    public function it_invalidates_expired_session()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($session->isValid());
    }

    #[Test]
    public function it_invalidates_inactive_session()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => false,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertFalse($session->isValid());
    }

    #[Test]
    public function it_checks_if_session_is_expired()
    {
        $user = User::factory()->create();
        $expiredSession = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $activeSession = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test2',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertTrue($expiredSession->isExpired());
        $this->assertFalse($activeSession->isExpired());
    }

    #[Test]
    public function it_scopes_sessions_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        TelegramSession::create([
            'user_id' => $user1->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test1',
            'is_active' => true,
        ]);

        TelegramSession::create([
            'user_id' => $user2->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test2',
            'is_active' => true,
        ]);

        $user1Sessions = TelegramSession::forUser($user1->id)->get();
        $this->assertCount(1, $user1Sessions);
        $this->assertEquals($user1->id, $user1Sessions->first()->user_id);
    }

    #[Test]
    public function it_scopes_only_active_sessions()
    {
        $user = User::factory()->create();

        TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test1',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test2',
            'is_active' => false,
            'expires_at' => now()->addDays(30),
        ]);

        $activeSessions = TelegramSession::forUser($user->id)->active()->get();
        $this->assertCount(1, $activeSessions);
        $this->assertTrue($activeSessions->first()->is_active);
    }

    #[Test]
    public function it_scopes_expired_sessions()
    {
        $user = User::factory()->create();

        TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test1',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test2',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        $expiredSessions = TelegramSession::expired()->get();
        $this->assertCount(1, $expiredSessions);
    }

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);
    }

    #[Test]
    public function it_stores_ip_address_and_user_agent()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->assertEquals('192.168.1.1', $session->ip_address);
        $this->assertEquals('Mozilla/5.0', $session->user_agent);
    }
}
