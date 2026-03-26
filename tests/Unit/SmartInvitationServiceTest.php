<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\SmartInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SmartInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private \Mockery\MockInterface $graph;
    private SmartInvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->graph = Mockery::mock(GraphRepositoryInterface::class);
        $this->graph->shouldReceive('ensurePersonNode')->andReturn([])->byDefault();
        $this->graph->shouldReceive('linkPersons')->andReturnNull()->byDefault();
        $this->graph->shouldReceive('removePersonNode')->andReturnNull()->byDefault();
        $this->graph->shouldReceive('getDescendantUuids')->andReturn([])->byDefault();

        $this->app->instance(GraphRepositoryInterface::class, $this->graph);

        $this->service = new SmartInvitationService($this->graph);
    }

    public function test_resolve_invitees_returns_empty_when_event_has_no_ancestor_node(): void
    {
        $creator = User::factory()->create();

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'No Ancestor Event',
            'starts_at' => now()->addDay(),
            'status' => 'draft',
            'ancestor_node_id' => null,
        ]);

        $invitees = $this->service->resolveInvitees($event);

        $this->assertCount(0, $invitees);
    }

    public function test_resolve_invitees_maps_descendant_uuids_to_users(): void
    {
        $creator = User::factory()->create();
        $inviteeA = User::factory()->create();
        $inviteeB = User::factory()->create();

        $inviteeA->profile()->update([
            'graph_node_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'full_name' => 'Invitee A',
        ]);

        $inviteeB->profile()->update([
            'graph_node_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'full_name' => 'Invitee B',
        ]);

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'Graph Event',
            'starts_at' => now()->addDays(3),
            'status' => 'draft',
            'ancestor_node_id' => 'ancestor-uuid',
            'invitation_depth' => 4,
        ]);

        $this->graph->shouldReceive('getDescendantUuids')
            ->once()
            ->with('ancestor-uuid', 4)
            ->andReturn([
                'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            ]);

        $invitees = $this->service->resolveInvitees($event);

        $this->assertCount(2, $invitees);
        $this->assertEqualsCanonicalizing(
            [$inviteeA->id, $inviteeB->id],
            $invitees->pluck('id')->all()
        );
    }

    public function test_dispatch_invitations_creates_pending_rsvps_without_duplicates(): void
    {
        $creator = User::factory()->create();
        $invitee = User::factory()->create();

        $invitee->profile()->update([
            'graph_node_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'full_name' => 'Invitee',
        ]);

        $event = Event::query()->create([
            'creator_id' => $creator->id,
            'name' => 'Dispatch Event',
            'starts_at' => now()->addDays(2),
            'status' => 'draft',
            'ancestor_node_id' => 'ancestor-dispatch',
            'invitation_depth' => 2,
        ]);

        $this->graph->shouldReceive('getDescendantUuids')
            ->twice()
            ->with('ancestor-dispatch', 2)
            ->andReturn(['cccccccc-cccc-4ccc-8ccc-cccccccccccc']);

        $this->service->dispatchInvitations($event);
        $this->service->dispatchInvitations($event);

        $this->assertDatabaseCount('rsvps', 1);

        $rsvp = Rsvp::query()->first();
        $this->assertSame($event->id, $rsvp->event_id);
        $this->assertSame($invitee->id, $rsvp->user_id);
        $this->assertSame('pending', $rsvp->status);
    }
}
