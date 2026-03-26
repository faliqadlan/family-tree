<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\Contracts\SmartInvitationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventApiTest extends TestCase
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

    public function test_events_routes_require_authentication(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
    }

    public function test_user_can_create_and_show_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Annual Reunion',
            'description' => 'Main family event',
            'location' => 'City Hall',
            'starts_at' => now()->addWeek()->toISOString(),
            'status' => 'draft',
        ];

        $createResponse = $this->postJson('/api/events', $payload);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('name', 'Annual Reunion')
            ->assertJsonPath('creator_id', $user->id);

        $eventId = $createResponse->json('id');

        $this->getJson("/api/events/{$eventId}")
            ->assertOk()
            ->assertJsonPath('id', $eventId)
            ->assertJsonPath('name', 'Annual Reunion');
    }

    public function test_create_event_returns_validation_errors(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/events', [
            'location' => 'Somewhere',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'starts_at']);
    }

    public function test_non_creator_cannot_update_event(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'Closed Planning',
            'starts_at' => now()->addDays(3),
            'status' => 'draft',
        ]);

        Sanctum::actingAs($otherUser);

        $this->patchJson("/api/events/{$event->id}", [
            'name' => 'Updated Name',
        ])->assertForbidden();
    }

    public function test_creator_can_update_and_delete_event(): void
    {
        $creator = User::factory()->create();

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'Planning Session',
            'starts_at' => now()->addDays(2),
            'status' => 'draft',
        ]);

        Sanctum::actingAs($creator);

        $this->patchJson("/api/events/{$event->id}", [
            'name' => 'Updated Planning Session',
            'status' => 'published',
        ])->assertOk()->assertJsonPath('name', 'Updated Planning Session');

        $this->deleteJson("/api/events/{$event->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_dispatch_invitations_uses_service(): void
    {
        $creator = User::factory()->create();

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'Invitation Trigger',
            'starts_at' => now()->addDays(5),
            'status' => 'draft',
        ]);

        $this->mock(SmartInvitationServiceInterface::class, function ($mock) use ($event): void {
            $mock->shouldReceive('dispatchInvitations')
                ->once()
                ->withArgs(fn (Event $incomingEvent): bool => $incomingEvent->id === $event->id);
        });

        Sanctum::actingAs($creator);

        $this->postJson("/api/events/{$event->id}/dispatch-invitations")
            ->assertOk()
            ->assertJsonPath('message', 'Invitations dispatched successfully.');
    }
}
