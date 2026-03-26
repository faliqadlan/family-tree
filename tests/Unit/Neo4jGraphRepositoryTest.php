<?php

namespace Tests\Unit;

use App\Repositories\Neo4jGraphRepository;
use App\Services\Neo4j\Neo4jService;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Neo4jGraphRepository::class)]
class Neo4jGraphRepositoryTest extends TestCase
{
    public function test_ensure_person_node_delegates_to_service(): void
    {
        $neo4j = Mockery::mock(Neo4jService::class);
        $neo4j->shouldReceive('mergePersonNode')
            ->once()
            ->with('uuid-1', 'Person One')
            ->andReturn(['uuid' => 'uuid-1', 'name' => 'Person One']);

        $repository = new Neo4jGraphRepository($neo4j);

        $result = $repository->ensurePersonNode('uuid-1', 'Person One');

        $this->assertSame('uuid-1', $result['uuid']);
        $this->assertSame('Person One', $result['name']);
    }

    public function test_link_persons_delegates_to_service(): void
    {
        $neo4j = Mockery::mock(Neo4jService::class);
        $neo4j->shouldReceive('createRelationship')
            ->once()
            ->with('parent-uuid', 'child-uuid', 'FATHER_OF');

        $repository = new Neo4jGraphRepository($neo4j);

        $repository->linkPersons('parent-uuid', 'child-uuid', 'FATHER_OF');

        $this->assertTrue(true);
    }

    public function test_get_descendant_uuids_delegates_to_service(): void
    {
        $neo4j = Mockery::mock(Neo4jService::class);
        $neo4j->shouldReceive('getDescendants')
            ->once()
            ->with('ancestor-uuid', 4)
            ->andReturn(['child-1', 'child-2']);

        $repository = new Neo4jGraphRepository($neo4j);

        $result = $repository->getDescendantUuids('ancestor-uuid', 4);

        $this->assertSame(['child-1', 'child-2'], $result);
    }

    public function test_remove_person_node_delegates_to_service(): void
    {
        $neo4j = Mockery::mock(Neo4jService::class);
        $neo4j->shouldReceive('deletePersonNode')
            ->once()
            ->with('uuid-delete');

        $repository = new Neo4jGraphRepository($neo4j);

        $repository->removePersonNode('uuid-delete');

        $this->assertTrue(true);
    }
}
