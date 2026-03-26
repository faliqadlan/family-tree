<?php

namespace Tests\Unit;

use App\Models\AccessRequest;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\PrivacyEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrivacyEngineService $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new PrivacyEngineService();

        // Mock the graph repository so the UserObserver doesn't try to connect to Neo4j
        $this->mock(GraphRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('ensurePersonNode')->andReturn([]);
            $mock->shouldReceive('linkPersons')->andReturn(null);
            $mock->shouldReceive('getDescendantUuids')->andReturn([]);
            $mock->shouldReceive('removePersonNode')->andReturn(null);
        });
    }

    public function test_owner_can_see_all_own_fields(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id'       => $user->id,
            'phone'         => '081234567890',
            'phone_privacy' => 'private',
        ]);

        $this->assertTrue($this->engine->canViewField($profile, $user, 'phone'));
    }

    public function test_private_field_hidden_from_others(): void
    {
        $owner  = User::factory()->create();
        $viewer = User::factory()->create();

        $profile = Profile::factory()->create([
            'user_id'       => $owner->id,
            'phone'         => '081234567890',
            'phone_privacy' => 'private',
        ]);

        $this->assertFalse($this->engine->canViewField($profile, $viewer, 'phone'));
    }

    public function test_masked_field_visible_after_approved_access_request(): void
    {
        $owner  = User::factory()->create();
        $viewer = User::factory()->create();

        $profile = Profile::factory()->create([
            'user_id'       => $owner->id,
            'phone'         => '081234567890',
            'phone_privacy' => 'masked',
        ]);

        AccessRequest::factory()->create([
            'requester_id'     => $viewer->id,
            'target_id'        => $owner->id,
            'requested_fields' => ['phone'],
            'status'           => 'approved',
        ]);

        $this->assertTrue($this->engine->canViewField($profile, $viewer, 'phone'));
    }

    public function test_sanitize_masks_private_fields(): void
    {
        $owner  = User::factory()->create();
        $viewer = User::factory()->create();

        $profile = Profile::factory()->create([
            'user_id'         => $owner->id,
            'phone'           => '081234567890',
            'phone_privacy'   => 'masked',
            'address'         => 'Jl. Sudirman No. 1',
            'address_privacy' => 'private',
        ]);

        $sanitized = $this->engine->sanitizeForViewer($profile, $viewer);

        $this->assertEquals('***', $sanitized['phone']);
        $this->assertNull($sanitized['address']);
    }

    public function test_super_admin_can_see_private_fields(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $profile = Profile::factory()->create([
            'user_id' => $owner->id,
            'phone' => '081234567890',
            'phone_privacy' => 'private',
        ]);

        $this->assertTrue($this->engine->canViewField($profile, $admin, 'phone'));
    }
}
