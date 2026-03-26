<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyTree\DescendantsRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Http\JsonResponse;

class FamilyTreeController extends Controller
{
    public function __construct(protected GraphRepositoryInterface $graph) {}

    /**
     * Get all descendants of an ancestor node.
     */
    public function descendants(DescendantsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $allowedAncestor = $user->profile?->graph_node_id;

            abort_unless($allowedAncestor, 403, 'Your account is not linked to a family branch yet.');
            abort_unless($validated['ancestor_uuid'] === $allowedAncestor, 403, 'You can only access your authorized family branch.');
        }

        $uuids = $this->graph->getDescendantUuids(
            $validated['ancestor_uuid'],
            $validated['depth'] ?? 4
        );

        // Resolve UUIDs to profile data
        $profiles = Profile::whereIn('graph_node_id', $uuids)
            ->with('user:id,name,email')
            ->get(['id', 'graph_node_id', 'full_name', 'gender', 'user_id']);

        return response()->json(ProfileResource::collection($profiles));
    }
}
