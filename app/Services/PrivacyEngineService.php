<?php

namespace App\Services;

use App\Models\AccessRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\Contracts\PrivacyEngineInterface;

class PrivacyEngineService implements PrivacyEngineInterface
{
    private const PRIVACY_FIELDS = ['phone', 'address', 'email', 'date_of_birth'];
    private const PRIVACY_COLUMN_MAP = [
        'phone'         => 'phone_privacy',
        'email'         => 'email_privacy',
        'date_of_birth' => 'dob_privacy',
        'address'       => 'address_privacy',
    ];

    public function getVisibleFields(Profile $profile, User $viewer): array
    {
        $visible = [];
        foreach (self::PRIVACY_FIELDS as $field) {
            if ($this->canViewField($profile, $viewer, $field)) {
                $visible[] = $field;
            }
        }
        return $visible;
    }

    public function canViewField(Profile $profile, User $viewer, string $field): bool
    {
        // Owner can always see their own data
        if ($profile->user_id === $viewer->id) {
            return true;
        }

        $privacyColumn = self::PRIVACY_COLUMN_MAP[$field] ?? null;
        if ($privacyColumn === null) {
            return true; // Non-privacy field – always visible
        }

        $privacyState = $profile->{$privacyColumn};

        return match ($privacyState) {
            'public'  => true,
            'private' => false,
            'masked'  => $this->hasApprovedAccess($profile->user_id, $viewer->id, $field),
            default   => false,
        };
    }

    public function sanitizeForViewer(Profile $profile, User $viewer): array
    {
        $data = $profile->toArray();

        foreach (self::PRIVACY_FIELDS as $field) {
            if (!$this->canViewField($profile, $viewer, $field)) {
                $privacyColumn = self::PRIVACY_COLUMN_MAP[$field] ?? null;
                $state = $privacyColumn ? $profile->{$privacyColumn} : 'private';
                $data[$field] = $state === 'masked' ? '***' : null;
            }
        }

        return $data;
    }

    private function hasApprovedAccess(int $ownerId, int $viewerId, string $field): bool
    {
        return AccessRequest::where('requester_id', $viewerId)
            ->where('target_id', $ownerId)
            ->where('status', 'approved')
            ->whereJsonContains('requested_fields', $field)
            ->exists();
    }
}
