<?php

namespace App\Services\Contracts;

use App\Models\Event;
use Illuminate\Support\Collection;

interface SmartInvitationServiceInterface
{
    /**
     * Resolve UUIDs of family members who should be invited based on
     * the event's ancestor_node_id and invitation_depth.
     *
     * @return Collection<int, \App\Models\User>
     */
    public function resolveInvitees(Event $event): Collection;

    /**
     * Dispatch invitations (create RSVPs + notifications) for the event.
     */
    public function dispatchInvitations(Event $event): void;
}
