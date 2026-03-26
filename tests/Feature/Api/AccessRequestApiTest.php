<?php

namespace Tests\Feature\Api;

use App\Models\AccessRequest;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccessRequestApiTest extends TestCase
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

    public function test_access_request_routes_require_authentication(): void
    {
        $this->getJson('/api/access-requests')->assertUnauthorized();
    }

    public function test_user_can_create_and_list_access_requests(): void
    {
        $requester = User::factory()->create();
        $target = User::factory()->create();

        Sanctum::actingAs($requester);

        $this->postJson('/api/access-requests', [
            'target_id' => $target->id,
            'requested_fields' => ['phone', 'email'],
            'requester_message' => 'Please grant access for invitation updates.',
        ])->assertCreated()->assertJsonPath('status', 'pending');

        $this->getJson('/api/access-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.target_id', $target->id);
    }

    public function test_create_access_request_validates_target_id_and_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/access-requests', [
            'target_id' => $user->id,
            'requested_fields' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['target_id', 'requested_fields']);
    }

    public function test_requester_can_update_pending_request(): void
    {
        $requester = User::factory()->create();
        $target = User::factory()->create();

        $accessRequest = AccessRequest::query()->create([
            'requester_id' => $requester->id,
            'target_id' => $target->id,
            'requested_fields' => ['phone'],
            'status' => 'pending',
        ]);

        Sanctum::actingAs($requester);

        $this->patchJson("/api/access-requests/{$accessRequest->id}", [
            'requested_fields' => ['phone', 'date_of_birth'],
            'requester_message' => 'Added one more field',
        ])->assertOk()->assertJsonPath('requester_message', 'Added one more field');
    }

    public function test_non_requester_cannot_update_access_request(): void
    {
        $requester = User::factory()->create();
        $target = User::factory()->create();
        $other = User::factory()->create();

        $accessRequest = AccessRequest::query()->create([
            'requester_id' => $requester->id,
            'target_id' => $target->id,
            'requested_fields' => ['phone'],
            'status' => 'pending',
        ]);

        Sanctum::actingAs($other);

        $this->patchJson("/api/access-requests/{$accessRequest->id}", [
            'requested_fields' => ['email'],
        ])->assertForbidden();
    }

    public function test_target_can_respond_and_requester_can_delete_access_request(): void
    {
        $requester = User::factory()->create();
        $target = User::factory()->create();

        $accessRequest = AccessRequest::query()->create([
            'requester_id' => $requester->id,
            'target_id' => $target->id,
            'requested_fields' => ['address'],
            'status' => 'pending',
        ]);

        Sanctum::actingAs($target);

        $this->patchJson("/api/access-requests/{$accessRequest->id}/respond", [
            'status' => 'approved',
            'target_response' => 'Approved for this event cycle.',
        ])->assertOk()->assertJsonPath('status', 'approved');

        Sanctum::actingAs($requester);

        $this->deleteJson("/api/access-requests/{$accessRequest->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('access_requests', ['id' => $accessRequest->id]);
    }
}
