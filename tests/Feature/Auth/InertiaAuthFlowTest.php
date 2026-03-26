<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InertiaAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(GraphRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('ensurePersonNode')->andReturn([]);
            $mock->shouldReceive('linkPersons')->andReturnNull();
            $mock->shouldReceive('getDescendantUuids')->andReturn([]);
            $mock->shouldReceive('removePersonNode')->andReturnNull();
        });
    }

    public function test_login_redirects_to_intended_inertia_page(): void
    {
        $user = User::factory()->create();

        $this->get('/family-tree')->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/family-tree');
    }

    public function test_invalid_login_flashes_errors_for_inertia_forms(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_profile_update_validation_errors_are_flashed_to_session(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => '',
                'email' => 'not-an-email',
            ]);

        $response
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_login_regenerates_session_id(): void
    {
        $user = User::factory()->create();

        $this->startSession();
        $initialSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertNotSame($initialSessionId, session()->getId());
    }

    public function test_sanctum_csrf_cookie_endpoint_sets_protection_cookies(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    }
}
