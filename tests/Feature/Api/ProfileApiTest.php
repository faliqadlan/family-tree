<?php

namespace Tests\Feature\Api;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
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

    public function test_profile_routes_require_authentication(): void
    {
        $this->getJson('/api/profiles')->assertUnauthorized();
    }

    public function test_admin_can_create_profile_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        $target->profile()?->delete();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/profiles', [
            'user_id' => $target->id,
            'full_name' => 'Created Profile',
            'gender' => 'other',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user_id', $target->id)
            ->assertJsonPath('full_name', 'Created Profile');
    }

    public function test_non_admin_cannot_create_profile(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $target = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/profiles', [
            'user_id' => $target->id,
            'full_name' => 'Denied Profile',
        ])->assertForbidden();
    }

    public function test_owner_can_update_and_delete_own_profile(): void
    {
        $owner = User::factory()->create();
        $profile = $owner->profile()->firstOrFail();

        Sanctum::actingAs($owner);

        $this->patchJson("/api/profiles/{$profile->id}", [
            'full_name' => 'Updated Owner Name',
            'bio' => 'Updated profile bio',
        ])->assertOk()->assertJsonPath('full_name', 'Updated Owner Name');

        $this->deleteJson("/api/profiles/{$profile->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
    }

    public function test_non_owner_cannot_update_profile(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create(['role' => 'member']);
        $profile = $owner->profile()->firstOrFail();

        Sanctum::actingAs($nonOwner);

        $this->patchJson("/api/profiles/{$profile->id}", [
            'full_name' => 'Hacked Name',
        ])->assertForbidden();
    }

    public function test_profile_update_returns_validation_errors_for_invalid_payload(): void
    {
        $owner = User::factory()->create();
        $profile = $owner->profile()->firstOrFail();

        Sanctum::actingAs($owner);

        $this->patchJson("/api/profiles/{$profile->id}", [
            'gender' => 'invalid-value',
        ])->assertUnprocessable()->assertJsonValidationErrors(['gender']);
    }

    public function test_profile_show_returns_data_for_authenticated_user(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $profile = $owner->profile()->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->getJson("/api/profiles/{$profile->id}")
            ->assertOk()
            ->assertJsonPath('id', $profile->id);
    }
}
