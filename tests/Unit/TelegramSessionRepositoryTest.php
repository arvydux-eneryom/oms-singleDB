<?php

namespace Tests\Unit;

use App\Models\TelegramSession;
use App\Models\User;
use App\Repositories\TelegramSessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TelegramSessionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TelegramSessionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TelegramSessionRepository;
    }

    #[Test]
    public function it_creates_session_with_random_identifier()
    {
        $user = User::factory()->create();

        $session = $this->repository->createSession(
            $user->id,
            '192.168.1.1',
            'Mozilla/5.0'
        );

        $this->assertInstanceOf(TelegramSession::class, $session);
        $this->assertEquals(64, strlen($session->session_identifier));
        $this->assertEquals($user->id, $session->user_id);
        $this->assertEquals('192.168.1.1', $session->ip_address);
        $this->assertEquals('Mozilla/5.0', $session->user_agent);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->expires_at);
    }

    #[Test]
    public function it_creates_session_with_custom_expiration()
    {
        $user = User::factory()->create();

        $session = $this->repository->createSession(
            $user->id,
            null,
            null,
            7 // 7 days
        );

        $expectedExpiration = now()->addDays(7);
        $this->assertEquals(
            $expectedExpiration->format('Y-m-d'),
            $session->expires_at->format('Y-m-d')
        );
    }

    #[Test]
    public function it_does_not_deactivate_existing_sessions_by_default()
    {
        $user = User::factory()->create();

        $firstSession = $this->repository->createSession($user->id);
        $secondSession = $this->repository->createSession($user->id);

        $firstSession->refresh();
        $this->assertTrue($firstSession->is_active);
        $this->assertTrue($secondSession->is_active);
    }

    #[Test]
    public function it_deactivates_existing_sessions_when_requested()
    {
        $user = User::factory()->create();

        $firstSession = $this->repository->createSession($user->id);
        $secondSession = $this->repository->createSession(
            $user->id,
            null,
            null,
            null,
            true // deactivate existing
        );

        $firstSession->refresh();
        $this->assertFalse($firstSession->is_active);
        $this->assertTrue($secondSession->is_active);
    }

    #[Test]
    public function it_gets_active_session_for_user()
    {
        $user = User::factory()->create();
        $session = $this->repository->createSession($user->id);

        $retrieved = $this->repository->getActiveSession($user->id);

        $this->assertInstanceOf(TelegramSession::class, $retrieved);
        $this->assertEquals($session->id, $retrieved->id);
    }

    #[Test]
    public function it_returns_null_when_no_active_session()
    {
        $user = User::factory()->create();

        $retrieved = $this->repository->getActiveSession($user->id);

        $this->assertNull($retrieved);
    }

    #[Test]
    public function it_gets_session_by_identifier()
    {
        $user = User::factory()->create();
        $session = $this->repository->createSession($user->id);

        $retrieved = $this->repository->getSessionByIdentifier($session->session_identifier);

        $this->assertInstanceOf(TelegramSession::class, $retrieved);
        $this->assertEquals($session->id, $retrieved->id);
    }

    #[Test]
    public function it_updates_session_activity()
    {
        $user = User::factory()->create();
        $session = $this->repository->createSession($user->id);

        $originalActivity = $session->last_activity_at;
        sleep(1);

        $this->repository->updateActivity($session);

        $session->refresh();
        $this->assertNotEquals($originalActivity, $session->last_activity_at);
    }

    #[Test]
    public function it_deactivates_session()
    {
        $user = User::factory()->create();
        $session = $this->repository->createSession($user->id);

        $this->repository->deactivateSession($session);

        $session->refresh();
        $this->assertFalse($session->is_active);
    }

    #[Test]
    public function it_deactivates_all_user_sessions()
    {
        $user = User::factory()->create();

        $session1 = $this->repository->createSession($user->id);
        $session2 = $this->repository->createSession($user->id);

        $this->repository->deactivateUserSessions($user->id);

        $session1->refresh();
        $session2->refresh();

        $this->assertFalse($session1->is_active);
        $this->assertFalse($session2->is_active);
    }

    #[Test]
    public function it_validates_session_ownership()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $session = $this->repository->createSession($user1->id);

        $this->assertTrue($this->repository->validateSessionOwnership($session, $user1->id));
        $this->assertFalse($this->repository->validateSessionOwnership($session, $user2->id));
    }

    #[Test]
    public function it_invalidates_expired_session_ownership()
    {
        $user = User::factory()->create();
        $session = TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/test',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($this->repository->validateSessionOwnership($session, $user->id));
    }

    #[Test]
    public function it_gets_session_stats()
    {
        $user = User::factory()->create();
        $session = $this->repository->createSession($user->id);

        $stats = $this->repository->getSessionStats($user->id);

        $this->assertTrue($stats['has_active_session']);
        $this->assertEquals($session->id, $stats['session_id']);
        $this->assertNotNull($stats['last_activity']);
        $this->assertNotNull($stats['expires_at']);
    }

    #[Test]
    public function it_returns_empty_stats_when_no_session()
    {
        $user = User::factory()->create();

        $stats = $this->repository->getSessionStats($user->id);

        $this->assertFalse($stats['has_active_session']);
        $this->assertNull($stats['session_id']);
    }

    #[Test]
    public function it_cleans_up_expired_sessions()
    {
        $user = User::factory()->create();

        // Create expired session
        TelegramSession::create([
            'user_id' => $user->id,
            'session_identifier' => TelegramSession::generateSecureIdentifier(),
            'session_path' => '/tmp/expired',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        // Create active session
        $this->repository->createSession($user->id);

        $count = $this->repository->cleanupExpiredSessions();

        $this->assertEquals(1, $count);
        $this->assertCount(1, TelegramSession::where('is_active', true)->get());
    }

    #[Test]
    public function it_creates_secure_directory_with_proper_permissions()
    {
        $user = User::factory()->create();

        $session = $this->repository->createSession($user->id);

        $this->assertDirectoryExists($session->session_path);

        // Check permissions (0700 = owner only)
        $perms = fileperms($session->session_path);
        $this->assertEquals(0700, $perms & 0777);

        // Cleanup
        File::deleteDirectory($session->session_path);
    }
}
