<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Services\Contracts\PrivacyEngineInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected PrivacyEngineInterface $privacyEngine) {}

    public function index(Request $request)
    {
        $profiles = Profile::with('user')->latest()->paginate(20);

        $profiles->through(fn(Profile $profile) => $this->privacyEngine->sanitizeForViewer($profile, $request->user()));

        return ProfileResource::collection($profiles);
    }

    public function store(StoreProfileRequest $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $profile = Profile::create($request->validated());

        return response()->json(ProfileResource::make($profile->load('user')), 201);
    }

    public function show(Request $request, Profile $profile): JsonResponse
    {
        $this->authorize('view', $profile);

        $sanitized = $this->privacyEngine->sanitizeForViewer($profile, $request->user());

        return response()->json(ProfileResource::make($sanitized));
    }

    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        $this->authorize('update', $profile);

        $profile->update($request->validated());

        return response()->json(ProfileResource::make($profile->fresh()->load('user')));
    }

    public function destroy(Request $request, Profile $profile): JsonResponse
    {
        $this->authorize('delete', $profile);

        $profile->delete();

        return response()->json(null, 204);
    }
}
