<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Profile $profile): bool
    {
        return true; // Visibility of fields is governed by PrivacyEngine
    }

    public function update(User $user, Profile $profile): bool
    {
        return $user->isSuperAdmin() || $user->id === $profile->user_id;
    }

    public function delete(User $user, Profile $profile): bool
    {
        return $user->isSuperAdmin() || $user->id === $profile->user_id;
    }
}
