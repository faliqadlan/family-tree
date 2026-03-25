<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = AccessRequest::where('target_id', $request->user()->id)
            ->with('requester')
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_id'          => 'required|exists:users,id|different:' . $request->user()->id,
            'requested_fields'   => 'required|array|min:1',
            'requested_fields.*' => 'in:phone,email,address,date_of_birth',
            'requester_message'  => 'nullable|string|max:500',
        ]);

        $accessRequest = AccessRequest::updateOrCreate(
            ['requester_id' => $request->user()->id, 'target_id' => $validated['target_id']],
            [
                'requested_fields'  => $validated['requested_fields'],
                'status'            => 'pending',
                'requester_message' => $validated['requester_message'] ?? null,
            ]
        );

        return response()->json($accessRequest, 201);
    }

    public function respond(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        abort_if($accessRequest->target_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'status'          => 'required|in:approved,rejected',
            'target_response' => 'nullable|string|max:500',
        ]);

        $accessRequest->update([
            'status'          => $validated['status'],
            'target_response' => $validated['target_response'] ?? null,
            'responded_at'    => now(),
        ]);

        return response()->json($accessRequest);
    }
}
