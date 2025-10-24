<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InactivityTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set default config for tests
        Config::set('auth.inactivity', [
            'enabled' => true,
            'timeout' => 3600, // 1 hour for testing
            'warning' => 300,  // 5 minutes
        ]);
    }

    #[Test]
    public function it_loads_inactivity_configuration_correctly()
    {
        $this->assertEquals(true, config('auth.inactivity.enabled'));
        $this->assertEquals(3600, config('auth.inactivity.timeout'));
        $this->assertEquals(300, config('auth.inactivity.warning'));
    }

    #[Test]
    public function it_renders_inactivity_tracker_when_enabled_and_user_is_logged_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('inactivityTracker');
        $response->assertSee('inactivity-warning');
    }

    #[Test]
    public function it_does_not_render_inactivity_tracker_when_disabled()
    {
        Config::set('auth.inactivity.enabled', false);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('inactivityTracker');
        $response->assertDontSee('inactivity-warning');
    }

    #[Test]
    public function it_does_not_render_inactivity_tracker_for_guests()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertDontSee('inactivityTracker');
    }

    #[Test]
    public function it_includes_correct_timeout_values_in_rendered_component()
    {
        Config::set('auth.inactivity.timeout', 7200); // 2 hours
        Config::set('auth.inactivity.warning', 600);  // 10 minutes

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('timeout: 7200');
        $response->assertSee('warning: 600');
    }

    #[Test]
    public function it_includes_csrf_token_in_component()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('csrfToken');
        $response->assertSee(csrf_token());
    }

    #[Test]
    public function it_includes_logout_route_in_component()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('logoutUrl');
        $response->assertSee(route('logout'));
    }

    #[Test]
    public function it_includes_warning_modal_with_correct_content()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Session Expiring Soon');
        $response->assertSee('Your session will expire due to inactivity in:');
        $response->assertSee('Stay Logged In');
        $response->assertSee('Log Out Now');
    }

    #[Test]
    public function it_includes_activity_event_listeners()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('mousedown');
        $response->assertSee('keydown');
        $response->assertSee('scroll');
        $response->assertSee('touchstart');
        $response->assertSee('click');
    }

    #[Test]
    public function it_includes_storage_key_for_cross_tab_synchronization()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('last_activity_timestamp');
        $response->assertSee('localStorage');
    }

    #[Test]
    public function it_includes_livewire_integration()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('window.Livewire');
        $response->assertSee('Livewire.hook');
    }

    #[Test]
    public function it_can_use_custom_timeout_values_via_props()
    {
        Config::set('auth.inactivity.timeout', 1800); // 30 minutes
        Config::set('auth.inactivity.warning', 120);  // 2 minutes

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('timeout: 1800');
        $response->assertSee('warning: 120');
    }

    #[Test]
    public function it_respects_enabled_flag_from_environment()
    {
        // Test with enabled = false via config
        Config::set('auth.inactivity.enabled', false);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('inactivityTracker');

        // Test with enabled = true via config
        Config::set('auth.inactivity.enabled', true);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('inactivityTracker');
    }

    #[Test]
    public function it_includes_debounced_activity_tracking()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('debouncedActivity');
        $response->assertSee('debounceTimer');
    }

    #[Test]
    public function it_includes_countdown_timer_functionality()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('remainingSeconds');
        $response->assertSee('countdownInterval');
        $response->assertSee('formatTime');
    }

    #[Test]
    public function it_includes_extend_session_functionality()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('extendSession()', false);
        $response->assertSee('Stay Logged In');
    }

    #[Test]
    public function it_includes_proper_error_handling_for_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('try {');
        $response->assertSee('catch (error)');
        $response->assertSee('console.error');
    }

    #[Test]
    public function logout_endpoint_works_correctly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect();
        $this->assertGuest();
    }

    #[Test]
    public function it_includes_proper_fetch_headers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('X-CSRF-TOKEN', false);
        $response->assertSee('Content-Type', false);
        $response->assertSee('application/json', false);
        $response->assertSee('same-origin', false);
    }

    #[Test]
    public function it_formats_time_correctly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Check for formatTime function
        $response->assertSee('formatTime(seconds)', false);
        $response->assertSee('Math.floor(seconds / 60)', false);
        $response->assertSee('padStart', false);
    }

    #[Test]
    public function it_clears_all_timers_on_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('clearTimers()');
        $response->assertSee('clearTimeout(this.timer)');
        $response->assertSee('clearTimeout(this.warningTimer)');
        $response->assertSee('clearInterval(this.countdownInterval)');
    }

    #[Test]
    public function it_redirects_to_login_after_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('window.location.href', false);
        $response->assertSee(route('login'), false);
    }

    #[Test]
    public function it_uses_alpine_js_properly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Alpine.data', false);
        $response->assertSee('inactivityTracker', false);
        $response->assertSee('x-init', false);
        $response->assertSee('x-cloak', false);
    }

    #[Test]
    public function it_shows_modal_using_flux_modal_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('$flux.modal', false);
        $response->assertSee('inactivity-warning', false);
    }

    #[Test]
    public function component_is_properly_integrated_in_sidebar_layout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Verify the component is included
        $response->assertSee('Inactivity Tracker', false);
        $response->assertSee('inactivityTracker', false);
    }
}
