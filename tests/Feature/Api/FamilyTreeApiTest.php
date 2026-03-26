<?php

namespace Tests\Feature\Api;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FamilyTreeApiTest extends TestCase
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

    public function test_descendants_route_requires_authentication(): void
    {
        $this->getJson('/api/family-tree')->assertUnauthorized();

        $this->getJson('/api/family-tree/descendants?ancestor_uuid=98c595ce-7bb4-423a-bc16-c538f7d5be4a')
            ->assertUnauthorized();
    }

    public function test_family_tree_route_returns_hierarchical_payload(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/family-tree');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'nodes',
                    'total_nodes',
                ],
            ]);
    }

    public function test_descendants_route_validates_required_ancestor_uuid(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/family-tree/descendants')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ancestor_uuid']);
    }

    public function test_descendants_route_returns_profiles_from_graph_uuids(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $this->mock(GraphRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('ensurePersonNode')->andReturn([]);
            $mock->shouldReceive('linkPersons')->andReturnNull();
            $mock->shouldReceive('removePersonNode')->andReturnNull();
            $mock->shouldReceive('getDescendantUuids')
                ->once()
                ->with('11111111-1111-4111-8111-111111111111', 3)
                ->andReturn([
                    '22222222-2222-4222-8222-222222222222',
                    '33333333-3333-4333-8333-333333333333',
                ]);
        });

        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $ownerA->profile()->update([
            'full_name' => 'Descendant A',
            'gender' => 'male',
            'graph_node_id' => '22222222-2222-4222-8222-222222222222',
        ]);

        $ownerB->profile()->update([
            'full_name' => 'Descendant B',
            'gender' => 'female',
            'graph_node_id' => '33333333-3333-4333-8333-333333333333',
        ]);

        $response = $this->getJson('/api/family-tree/descendants?ancestor_uuid=11111111-1111-4111-8111-111111111111&depth=3');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['full_name' => 'Descendant A'])
            ->assertJsonFragment(['full_name' => 'Descendant B']);
    }

    public function test_non_admin_cannot_query_other_ancestor_branch(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/family-tree/descendants?ancestor_uuid=11111111-1111-4111-8111-111111111111&depth=3')
            ->assertForbidden();
    }
}
