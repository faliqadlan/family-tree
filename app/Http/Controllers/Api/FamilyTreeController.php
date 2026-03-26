<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyTree\DescendantsRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\Contracts\PrivacyEngineInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FamilyTreeController extends Controller
{
    public function __construct(
        protected GraphRepositoryInterface $graph,
        protected PrivacyEngineInterface $privacyEngine
    ) {}

    /**
     * Get hierarchical family tree for current viewer.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $profiles = Profile::query()
                ->with('user:id,name,email')
                ->get([
                    'id',
                    'user_id',
                    'full_name',
                    'nickname',
                    'gender',
                    'date_of_birth',
                    'date_of_death',
                    'place_of_birth',
                    'bio',
                    'phone',
                    'phone_privacy',
                    'email_privacy',
                    'dob_privacy',
                    'address',
                    'address_privacy',
                    'father_name',
                    'mother_name',
                    'graph_node_id',
                    'created_at',
                    'updated_at',
                ]);
        } else {
            $allowedAncestor = $user->profile?->graph_node_id;

            abort_unless($allowedAncestor, 403, 'Your account is not linked to a family branch yet.');

            $uuids = $this->graph->getDescendantUuids($allowedAncestor, 10);
            $uuids[] = $allowedAncestor;
            $uuids = array_values(array_unique($uuids));

            $profiles = Profile::query()
                ->whereIn('graph_node_id', $uuids)
                ->with('user:id,name,email')
                ->get([
                    'id',
                    'user_id',
                    'full_name',
                    'nickname',
                    'gender',
                    'date_of_birth',
                    'date_of_death',
                    'place_of_birth',
                    'bio',
                    'phone',
                    'phone_privacy',
                    'email_privacy',
                    'dob_privacy',
                    'address',
                    'address_privacy',
                    'father_name',
                    'mother_name',
                    'graph_node_id',
                    'created_at',
                    'updated_at',
                ]);
        }

        $sanitizedProfiles = $profiles
            ->map(fn(Profile $profile): array => $this->privacyEngine->sanitizeForViewer($profile, $user))
            ->values();

        $tree = $this->buildTree($sanitizedProfiles->all());

        return response()->json([
            'data' => [
                'nodes' => $tree,
                'total_nodes' => $sanitizedProfiles->count(),
            ],
        ]);
    }

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

        return ProfileResource::collection($profiles)->response();
    }

    /**
     * @param  array<int, array<string, mixed>>  $profiles
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(array $profiles): array
    {
        $nodesById = [];
        $nameToIds = [];
        $childrenByParent = [];
        $hasParent = [];

        foreach ($profiles as $profile) {
            $id = (string) ($profile['id'] ?? '');

            if ($id === '') {
                continue;
            }

            $nodesById[$id] = $profile;
            $childrenByParent[$id] = [];

            $name = trim((string) ($profile['full_name'] ?? ''));
            if ($name !== '') {
                $nameToIds[$name] ??= [];
                $nameToIds[$name][] = $id;
            }
        }

        foreach ($nodesById as $id => $profile) {
            foreach (['father_name', 'mother_name'] as $parentField) {
                $parentName = trim((string) ($profile[$parentField] ?? ''));
                if ($parentName === '' || !isset($nameToIds[$parentName])) {
                    continue;
                }

                $parentId = $nameToIds[$parentName][0] ?? null;
                if (!$parentId || $parentId === $id) {
                    continue;
                }

                if (!in_array($id, $childrenByParent[$parentId], true)) {
                    $childrenByParent[$parentId][] = $id;
                }

                $hasParent[$id] = true;
            }
        }

        $rootIds = array_values(array_filter(
            array_keys($nodesById),
            fn(string $id): bool => !isset($hasParent[$id])
        ));

        if (empty($rootIds)) {
            $rootIds = array_keys($nodesById);
        }

        return array_values(array_map(
            fn(string $rootId): array => $this->buildNode($rootId, $nodesById, $childrenByParent),
            $rootIds
        ));
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  array<string, array<int, string>>  $childrenByParent
     * @param  array<string, bool>  $visited
     * @return array<string, mixed>
     */
    private function buildNode(
        string $id,
        array $nodesById,
        array $childrenByParent,
        array $visited = []
    ): array {
        $node = $nodesById[$id] ?? ['id' => $id, 'full_name' => 'Unknown'];

        if (isset($visited[$id])) {
            $node['children'] = [];

            return $node;
        }

        $visited[$id] = true;

        $children = array_map(
            fn(string $childId): array => $this->buildNode($childId, $nodesById, $childrenByParent, $visited),
            $childrenByParent[$id] ?? []
        );

        $node['children'] = $children;

        return $node;
    }
}
