<?php

namespace Tests\Feature\Inertia;

use App\Models\AccessRequest;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AppInertiaPagesTest extends TestCase
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

    public function test_welcome_page_renders_inertia_component(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('canLogin', true)
                ->where('canRegister', true)
                ->has('laravelVersion')
                ->has('phpVersion')
            );
    }

    public function test_dashboard_page_renders_with_stats_props(): void
    {
        $user = User::factory()->create();

        Event::query()->create([
            'creator_id' => $user->id,
            'name' => 'Family Gathering',
            'starts_at' => now()->addWeek(),
            'status' => 'draft',
        ]);

        $requester = User::factory()->create();

        AccessRequest::query()->create([
            'requester_id' => $requester->id,
            'target_id' => $user->id,
            'requested_fields' => ['phone'],
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.totalEvents', 1)
                ->where('stats.pendingAccessRequests', 1)
                ->where('stats.totalProfiles', 2)
            );
    }

    public function test_family_tree_page_renders_with_expected_props(): void
    {
        $user = User::factory()->create();
        $user->load('profile');

        $response = $this->actingAs($user)->get('/family-tree');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('FamilyTree')
                ->where('defaultDepth', 4)
                ->where('initialAncestorUuid', $user->profile?->graph_node_id)
            );
    }

    public function test_profile_management_page_renders_with_expected_props(): void
    {
        $user = User::factory()->create();
        $user->load('profile');

        $response = $this->actingAs($user)->get('/profile-management');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProfileManagement')
                ->where('profileId', $user->profile?->id)
            );
    }

    public function test_profile_edit_page_renders_inertia_component(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->where('mustVerifyEmail', false)
                ->where('status', null)
            );
    }

    public function test_guest_is_redirected_from_protected_inertia_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/family-tree')->assertRedirect(route('login'));
        $this->get('/profile-management')->assertRedirect(route('login'));
        $this->get('/profile')->assertRedirect(route('login'));
    }
}
