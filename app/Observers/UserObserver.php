<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Support\Str;

class UserObserver
{
    public function __construct(protected GraphRepositoryInterface $graph) {}

    /**
     * After a User is created, provision a Person node in Neo4j.
     */
    public function created(User $user): void
    {
        $graphNodeId = (string) Str::uuid();

        $this->graph->ensurePersonNode($graphNodeId, $user->name);

        // Persist the graph_node_id in the profiles table
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['graph_node_id' => $graphNodeId, 'full_name' => $user->name]
        );
    }
}
