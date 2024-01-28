<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\EntityClone;
use Tests\Tables\Drivers;
use Tests\Tables\DriversPayment;

class EntityCloneTest extends EntityCloneTestCommons
{
    public function testEntityCloneSimple()
    {
        $this->db->renewTable('source', ($driversTable = new Drivers()));
        $this->db->seed('source', $driversTable);

        $this->db->renewTable('destiny', $driversTable);

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityClone("6");

        $this->assertSame(
            1,
            $this->db->countEntries('source', $driversTable)
        );
        $this->assertSame(
            1,
            $this->db->countEntries('destiny', $driversTable)
        );
    }

    public function testEntityCloneDeep()
    {
        $driversTable = new Drivers();
        $driversPayment = new DriversPayment();

        $this->db->renewTable('source', $driversTable);
        $this->db->renewTable('destiny', $driversTable);
        $this->db->seed('source', $driversTable);
        $this->db->renewTable('source', $driversPayment);
        $this->db->renewTable('destiny', $driversPayment);
        $this->db->seed('source', $driversPayment);

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityCloneDeepByFieldName("6");

        $this->assertSame(
            1,
            $this->db->countEntries('destiny', $driversTable)
        );

        $this->assertSame(
            3,
            $this->db->countEntries('destiny', $driversPayment)
        );
    }
}
