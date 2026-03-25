<?php

namespace App\Observers;

use App\Models\Profile;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Support\Str;

class ProfileObserver
{
    public function __construct(protected GraphRepositoryInterface $graph) {}

    /**
     * When father_name or mother_name changes, auto-link nodes in Neo4j.
     */
    public function saved(Profile $profile): void
    {
        if ($profile->graph_node_id === null) {
            return;
        }

        if ($profile->wasChanged('father_name') && $profile->father_name) {
            $this->linkParent($profile, $profile->father_name, 'FATHER_OF');
        }

        if ($profile->wasChanged('mother_name') && $profile->mother_name) {
            $this->linkParent($profile, $profile->mother_name, 'MOTHER_OF');
        }
    }

    private function linkParent(Profile $profile, string $parentName, string $relType): void
    {
        // Try to find an existing profile with matching full_name
        $parentProfile = Profile::where('full_name', $parentName)->first();
        $parentUuid = $parentProfile?->graph_node_id ?? (string) Str::uuid();

        // Ensure parent node exists in Neo4j
        $this->graph->ensurePersonNode($parentUuid, $parentName);

        // Draw the directed edge: parent -[FATHER_OF|MOTHER_OF]-> child
        $this->graph->linkPersons($parentUuid, $profile->graph_node_id, $relType);
    }
}
