<?php

namespace App\Services\Contracts;

use App\Models\Profile;
use App\Models\User;

interface PrivacyEngineInterface
{
    /**
     * Return the profile fields that are visible to $viewer based on privacy settings
     * and any approved AccessRequests.
     */
    public function getVisibleFields(Profile $profile, User $viewer): array;

    /**
     * Determine if $viewer can access a specific field on the profile.
     */
    public function canViewField(Profile $profile, User $viewer, string $field): bool;

    /**
     * Apply masking rules and return a sanitized profile array for API responses.
     */
    public function sanitizeForViewer(Profile $profile, User $viewer): array;
}
