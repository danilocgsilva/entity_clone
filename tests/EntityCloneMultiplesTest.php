<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\EntityCloneMultiples;
use Tests\Tables\Drivers;

class EntityCloneMultiplesTest extends EntityCloneTestCommons
{
    public function testEntityCloneMultipleSimple()
    {
        $this->db->setDatabase('source')
            ->renewTable('source', ($driversTable = new Drivers()));

        $pdo = $this->db->getPdo();

        $driversTable->seedMultipleDrivers('source', $pdo);

        $this->db->setDatabase('destiny')
            ->renewTable('destiny', $driversTable);

        $entityClone = new EntityCloneMultiples(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityCloneMultiples([3, 4, 7]);

        $this->assertSame(
            4,
            $this->db->countEntries('source', $driversTable)
        );
        $this->assertSame(
            3,
            $this->db->countEntries('destiny', $driversTable)
        );
    }
}
