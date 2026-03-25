<?php

namespace App\Repositories;

use App\Repositories\Contracts\GraphRepositoryInterface;
use App\Services\Neo4j\Neo4jService;

class Neo4jGraphRepository implements GraphRepositoryInterface
{
    public function __construct(protected Neo4jService $neo4j) {}

    public function ensurePersonNode(string $uuid, string $name): array
    {
        return $this->neo4j->mergePersonNode($uuid, $name);
    }

    public function linkPersons(string $fromUuid, string $toUuid, string $relType): void
    {
        $this->neo4j->createRelationship($fromUuid, $toUuid, $relType);
    }

    public function getDescendantUuids(string $ancestorUuid, int $depth): array
    {
        return $this->neo4j->getDescendants($ancestorUuid, $depth);
    }

    public function removePersonNode(string $uuid): void
    {
        $this->neo4j->deletePersonNode($uuid);
    }
}
