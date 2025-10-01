<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class TenantGuestRoutesTest extends TestCase
{
    public function test_login_route_is_registered(): void
    {
        $this->assertTrue(Route::has('login'));

        $route = Route::getRoutes()->getByName('login');
        $this->assertNotNull($route);
        $this->assertEquals(['GET', 'HEAD'], $route->methods());
        $this->assertEquals('login', $route->uri());
    }

    public function test_register_route_is_registered(): void
    {
        $this->assertTrue(Route::has('register'));

        $route = Route::getRoutes()->getByName('register');
        $this->assertNotNull($route);
        $this->assertEquals(['GET', 'HEAD'], $route->methods());
        $this->assertEquals('register', $route->uri());
    }

    public function test_password_request_route_is_registered(): void
    {
        $this->assertTrue(Route::has('password.request'));

        $route = Route::getRoutes()->getByName('password.request');
        $this->assertNotNull($route);
        $this->assertEquals(['GET', 'HEAD'], $route->methods());
        $this->assertEquals('forgot-password', $route->uri());
    }

    public function test_password_reset_route_is_registered(): void
    {
        $this->assertTrue(Route::has('password.reset'));

        $route = Route::getRoutes()->getByName('password.reset');
        $this->assertNotNull($route);
        $this->assertEquals(['GET', 'HEAD'], $route->methods());
        $this->assertEquals('reset-password/{token}', $route->uri());
    }

    public function test_home_redirect_route_is_registered(): void
    {
        $this->assertTrue(Route::has('home'));

        $route = Route::getRoutes()->getByName('home');
        $this->assertNotNull($route);
        $this->assertContains('GET', $route->methods());
        $this->assertContains('HEAD', $route->methods());
        $this->assertEquals('/', $route->uri());
    }

    public function test_guest_routes_have_required_middleware(): void
    {
        $guestRoutes = ['login', 'register', 'password.request', 'password.reset', 'home'];

        foreach ($guestRoutes as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $middleware = $route->middleware();

            $this->assertContains('web', $middleware, "Route {$routeName} should have 'web' middleware");
            $this->assertContains('guest', $middleware, "Route {$routeName} should have 'guest' middleware");
            $this->assertContains(InitializeTenancyByDomain::class, $middleware, "Route {$routeName} should have InitializeTenancyByDomain middleware");
            $this->assertContains(PreventAccessFromCentralDomains::class, $middleware, "Route {$routeName} should have PreventAccessFromCentralDomains middleware");
        }
    }

    public function test_home_route_redirects_to_login(): void
    {
        $route = Route::getRoutes()->getByName('home');
        $action = $route->getAction();

        $this->assertArrayHasKey('uses', $action);
        $this->assertStringContainsString('redirect', strtolower($action['uses']));
    }

    public function test_volt_routes_are_properly_configured(): void
    {
        $voltRoutes = ['login', 'register', 'password.request', 'password.reset'];

        foreach ($voltRoutes as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $action = $route->getAction();

            $this->assertArrayHasKey('uses', $action, "Route {$routeName} should have a uses action defined");
            $this->assertNotEmpty($action['uses'], "Route {$routeName} should have a non-empty uses action");
        }
    }

    public function test_password_reset_route_has_token_parameter(): void
    {
        $route = Route::getRoutes()->getByName('password.reset');
        $parameterNames = $route->parameterNames();

        $this->assertContains('token', $parameterNames, 'Password reset route should have a token parameter');
    }

    public function test_all_guest_routes_are_accessible_via_get(): void
    {
        $routes = ['login', 'register', 'password.request', 'password.reset', 'home'];

        foreach ($routes as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $methods = $route->methods();

            $this->assertContains('GET', $methods, "Route {$routeName} should be accessible via GET");
            $this->assertContains('HEAD', $methods, "Route {$routeName} should be accessible via HEAD");
        }
    }
}
