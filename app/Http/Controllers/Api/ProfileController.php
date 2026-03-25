<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\Contracts\PrivacyEngineInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected PrivacyEngineInterface $privacyEngine) {}

    public function show(Request $request, Profile $profile): JsonResponse
    {
        $this->authorize('view', $profile);

        $sanitized = $this->privacyEngine->sanitizeForViewer($profile, $request->user());

        return response()->json($sanitized);
    }

    public function update(Request $request, Profile $profile): JsonResponse
    {
        $this->authorize('update', $profile);

        $validated = $request->validate([
            'full_name'      => 'sometimes|string|max:255',
            'nickname'       => 'sometimes|nullable|string|max:100',
            'gender'         => 'sometimes|nullable|in:male,female,other',
            'date_of_birth'  => 'sometimes|nullable|date',
            'date_of_death'  => 'sometimes|nullable|date',
            'place_of_birth' => 'sometimes|nullable|string|max:255',
            'bio'            => 'sometimes|nullable|string',
            'phone'          => 'sometimes|nullable|string|max:20',
            'phone_privacy'  => 'sometimes|in:public,private,masked',
            'email_privacy'  => 'sometimes|in:public,private,masked',
            'dob_privacy'    => 'sometimes|in:public,private,masked',
            'address'        => 'sometimes|nullable|string',
            'address_privacy'=> 'sometimes|in:public,private,masked',
            'father_name'    => 'sometimes|nullable|string|max:255',
            'mother_name'    => 'sometimes|nullable|string|max:255',
        ]);

        $profile->update($validated);

        return response()->json($profile->fresh());
    }
}
