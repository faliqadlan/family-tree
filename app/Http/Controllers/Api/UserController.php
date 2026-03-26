<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        return UserResource::collection(User::query()->latest()->paginate(20));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $user = User::create($request->validated());

        return response()->json(UserResource::make($user), 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->id === $user->id, 403);

        return response()->json(UserResource::make($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $isAdmin = $request->user()->isSuperAdmin();
        abort_unless($isAdmin || $request->user()->id === $user->id, 403);

        $validated = $request->validated();

        if (! $isAdmin) {
            unset($validated['role'], $validated['is_stub'], $validated['is_deceased'], $validated['email_verified_at']);
        }

        $user->update($validated);

        return response()->json(UserResource::make($user->fresh()));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $user->delete();

        return response()->json(null, 204);
    }
}
