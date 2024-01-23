<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\EntityClone;
use Tests\Tables\Drivers;

class EntityCloneTest extends EntityCloneTestCommons
{
    public function testEntityCloneStarting()
    {
        $this->db->seed('source');
        $this->assertSame(
            1,
            $this->db->countEntries('source', Drivers::TABLE_NAME)
        );
        $this->assertSame(
            0,
            $this->db->countEntries('destiny', Drivers::TABLE_NAME)
        );
    }

    public function testEntityCloneSimple()
    {
        $this->db->seed('source');

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityClone("6");

        $this->assertSame(
            1,
            $this->db->countEntries('source', Drivers::TABLE_NAME)
        );
        $this->assertSame(
            1,
            $this->db->countEntries('destiny', Drivers::TABLE_NAME)
        );
    }
}
