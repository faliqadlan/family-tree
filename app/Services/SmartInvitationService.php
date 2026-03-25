<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Profile;
use App\Models\Rsvp;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\Contracts\SmartInvitationServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmartInvitationService implements SmartInvitationServiceInterface
{
    public function __construct(protected GraphRepositoryInterface $graph) {}

    public function resolveInvitees(Event $event): Collection
    {
        if (!$event->ancestor_node_id) {
            return collect();
        }

        $descendantUuids = $this->graph->getDescendantUuids(
            $event->ancestor_node_id,
            $event->invitation_depth
        );

        if (empty($descendantUuids)) {
            return collect();
        }

        // Translate graph node UUIDs → SQL User IDs via the profiles table
        return User::whereHas(
            'profile',
            fn($q) => $q->whereIn('graph_node_id', $descendantUuids)
        )->get();
    }

    public function dispatchInvitations(Event $event): void
    {
        $invitees = $this->resolveInvitees($event);

        DB::transaction(function () use ($event, $invitees) {
            foreach ($invitees as $user) {
                Rsvp::firstOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    ['status' => 'pending', 'pax' => 1]
                );
            }
        });

        // TODO: Dispatch notification jobs for each invitee
    }
}
