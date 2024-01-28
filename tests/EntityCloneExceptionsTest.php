<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\EntityClone;
use Exception;
use Tests\Tables\Drivers;

class EntityCloneExceptionsTest extends EntityCloneTestCommons
{
    public function testPdoWithoutDatabaseSource()
    {
        $this->expectException(Exception::class);
        $this->db->seed('source', (new Drivers()));

        $entityClone = new EntityClone(
            $this->createPdo(),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityClone("6");
    }

    public function testPdoWithoutDatabaseDestiny()
    {
        $this->expectException(Exception::class);
        $this->db->seed('source', (new Drivers()));

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo()
        );
        
        $entityClone->setTable("drivers")
            ->entityClone("6");
    }

    public function testPdoWithoutDatabaseBoth()
    {
        $this->expectException(Exception::class);
        $this->db->seed('source', (new Drivers()));

        $entityClone = new EntityClone(
            $this->createPdo(),
            $this->createPdo()
        );
        
        $entityClone->setTable("drivers")
            ->entityClone("6");
    }
}
