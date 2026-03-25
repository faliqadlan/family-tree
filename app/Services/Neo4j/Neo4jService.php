<?php

namespace App\Services\Neo4j;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4jService
{
    protected ClientInterface $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver(
                'default',
                sprintf(
                    'bolt://%s:%s@%s:%d',
                    config('neo4j.username'),
                    config('neo4j.password'),
                    config('neo4j.host'),
                    config('neo4j.port')
                )
            )
            ->withDefaultDriver('default')
            ->build();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Run a Cypher query and return results.
     */
    public function run(string $cypher, array $params = []): \Laudis\Neo4j\Types\CypherList
    {
        return $this->client->run($cypher, $params);
    }

    /**
     * Create or merge a Person node in Neo4j.
     */
    public function mergePersonNode(string $uuid, string $name): array
    {
        $result = $this->client->run(
            'MERGE (p:Person {uuid: $uuid}) ON CREATE SET p.name = $name, p.created_at = datetime() ON MATCH SET p.name = $name RETURN p',
            ['uuid' => $uuid, 'name' => $name]
        );

        return $result->first()->get('p')->getProperties()->toArray();
    }

    /**
     * Create a directional relationship between two nodes.
     * $relType: FATHER_OF | MOTHER_OF | MARRIED_TO | SIBLING_OF | CHILD_OF
     */
    public function createRelationship(string $fromUuid, string $toUuid, string $relType): void
    {
        $this->client->run(
            "MATCH (a:Person {uuid: \$fromUuid}), (b:Person {uuid: \$toUuid}) MERGE (a)-[:{$relType}]->(b)",
            ['fromUuid' => $fromUuid, 'toUuid' => $toUuid]
        );
    }

    /**
     * Get all descendants of a node up to N generations.
     */
    public function getDescendants(string $ancestorUuid, int $depth = 4): array
    {
        $result = $this->client->run(
            'MATCH (ancestor:Person {uuid: $uuid})-[:FATHER_OF|MOTHER_OF*1..$depth]->(descendant:Person) RETURN DISTINCT descendant.uuid AS uuid',
            ['uuid' => $ancestorUuid, 'depth' => $depth]
        );

        return $result->map(fn($row) => $row->get('uuid'))->toArray();
    }

    /**
     * Delete a Person node and all its relationships.
     */
    public function deletePersonNode(string $uuid): void
    {
        $this->client->run(
            'MATCH (p:Person {uuid: $uuid}) DETACH DELETE p',
            ['uuid' => $uuid]
        );
    }
}
