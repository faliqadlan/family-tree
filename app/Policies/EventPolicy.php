<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventCommittee;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $event->status !== 'draft' || $this->isCommittee($user, $event) || $user->id === $event->creator_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->id === $event->creator_id || $this->isCommittee($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->id === $event->creator_id;
    }

    public function manageFinances(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isCommittee($user, $event, ['coordinator', 'treasurer']);
    }

    private function isCommittee(User $user, Event $event, array $roles = []): bool
    {
        $query = EventCommittee::where('event_id', $event->id)->where('user_id', $user->id);
        if (!empty($roles)) {
            $query->whereIn('role', $roles);
        }
        return $query->exists();
    }
}
