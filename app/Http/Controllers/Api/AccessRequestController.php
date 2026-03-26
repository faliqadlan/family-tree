<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccessRequest\RespondAccessRequestRequest;
use App\Http\Requests\AccessRequest\StoreAccessRequestRequest;
use App\Http\Requests\AccessRequest\UpdateAccessRequestRequest;
use App\Http\Resources\AccessRequestResource;
use App\Models\AccessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = AccessRequest::where(function ($query) use ($request) {
            $query->where('target_id', $request->user()->id)
                ->orWhere('requester_id', $request->user()->id);
        })
            ->with(['requester', 'target'])
            ->latest()
            ->paginate(20);

        return AccessRequestResource::collection($requests);
    }

    public function store(StoreAccessRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $accessRequest = AccessRequest::updateOrCreate(
            ['requester_id' => $request->user()->id, 'target_id' => $validated['target_id']],
            [
                'requested_fields'  => $validated['requested_fields'],
                'status'            => 'pending',
                'requester_message' => $validated['requester_message'] ?? null,
            ]
        );

        return response()->json(AccessRequestResource::make($accessRequest->load(['requester', 'target'])), 201);
    }

    public function show(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        abort_unless(
            $request->user()->isSuperAdmin()
                || $accessRequest->requester_id === $request->user()->id
                || $accessRequest->target_id === $request->user()->id,
            403
        );

        return response()->json(AccessRequestResource::make($accessRequest->load(['requester', 'target'])));
    }

    public function update(UpdateAccessRequestRequest $request, AccessRequest $accessRequest): JsonResponse
    {
        abort_unless($accessRequest->requester_id === $request->user()->id, 403);
        abort_unless($accessRequest->status === 'pending', 422, 'Only pending requests can be updated.');

        $accessRequest->update($request->validated());

        return response()->json(AccessRequestResource::make($accessRequest->fresh()->load(['requester', 'target'])));
    }

    public function respond(RespondAccessRequestRequest $request, AccessRequest $accessRequest): JsonResponse
    {
        abort_unless(
            $request->user()->isSuperAdmin() || $accessRequest->target_id === $request->user()->id,
            403
        );

        $validated = $request->validated();

        $accessRequest->update([
            'status'          => $validated['status'],
            'target_response' => $validated['target_response'] ?? null,
            'responded_at'    => now(),
        ]);

        return response()->json(AccessRequestResource::make($accessRequest->fresh()->load(['requester', 'target'])));
    }

    public function destroy(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        abort_unless(
            $request->user()->isSuperAdmin() || $accessRequest->requester_id === $request->user()->id,
            403
        );

        $accessRequest->delete();

        return response()->json(null, 204);
    }
}
