<?php

namespace App\Repositories\Contracts;

interface GraphRepositoryInterface
{
    /**
     * Ensure a Person node exists in Neo4j for the given UUID.
     */
    public function ensurePersonNode(string $uuid, string $name): array;

    /**
     * Link two person nodes with a typed directional relationship.
     */
    public function linkPersons(string $fromUuid, string $toUuid, string $relType): void;

    /**
     * Retrieve all descendant UUIDs up to N generations from an ancestor.
     */
    public function getDescendantUuids(string $ancestorUuid, int $depth): array;

    /**
     * Remove a person node from the graph.
     */
    public function removePersonNode(string $uuid): void;
}
