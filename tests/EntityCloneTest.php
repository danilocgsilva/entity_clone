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
        $this->db->setDatabase('source')
            ->renewTable('source', ($driversTable = new Drivers()))
            ->seed($driversTable);

        $this->db->setDatabase('destiny')
            ->renewTable('destiny', $driversTable);

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

        $this->db->setDatabase('source')
            ->renewTable('source', $driversTable)
            ->renewTable('source', $driversPayment);

        $this->db->setDatabase('destiny')
            ->renewTable('destiny', $driversTable)
            ->renewTable('destiny', $driversPayment);

        $this->db->setDatabase('source')
            ->seed($driversTable)
            ->seed($driversPayment);

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

    public function testCloneDeepAlongFilledTables()
    {
        $driverTable = new Drivers();
        $driverPaymentTable = new DriversPayment();
        $pdo = $this->db->getPdo();

        $this->db->setDatabase('source');
        $this->db->renewTable('source', $driverTable);
        $this->db->renewTable('source', $driverPaymentTable);

        $this->db->setDatabase('destiny');
        $this->db->renewTable('destiny', $driverTable);
        $this->db->renewTable('destiny', $driverPaymentTable);

        $driverTable->seedDefaultDriver('source', $pdo);
        $driverTable->seedAdditionalDriver('source', $pdo);

        $driverPaymentTable->seedDefaultPayments('source', $pdo);
        $driverPaymentTable->seedAdditionalDriverPayments('source', $pdo);

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityCloneDeepByFieldName("6");

        $this->assertSame(
            1,
            $this->db->countEntries('destiny', $driverTable)
        );
    
        $this->assertSame(
            3,
            $this->db->countEntries('destiny', $driverPaymentTable)
        );
    }

    public function testCloneDeepAlongFilledTablesSecondDriver()
    {
        $driverTable = new Drivers();
        $driverPaymentTable = new DriversPayment();
        $pdo = $this->db->getPdo();

        $this->db->setDatabase('source');
        $this->db->renewTable('source', $driverTable);
        $this->db->renewTable('source', $driverPaymentTable);

        $this->db->setDatabase('destiny');
        $this->db->renewTable('destiny', $driverTable);
        $this->db->renewTable('destiny', $driverPaymentTable);

        $driverTable->seedDefaultDriver('source', $pdo);
        $driverTable->seedAdditionalDriver('source', $pdo);

        $driverPaymentTable->seedDefaultPayments('source', $pdo);
        $driverPaymentTable->seedAdditionalDriverPayments('source', $pdo);

        $entityClone = new EntityClone(
            $this->createPdo('source'),
            $this->createPdo('destiny')
        );

        $entityClone->setTable("drivers")
            ->entityCloneDeepByFieldName("9");

        $this->assertSame(
            1,
            $this->db->countEntries('destiny', $driverTable)
        );
    
        $this->assertSame(
            4,
            $this->db->countEntries('destiny', $driverPaymentTable)
        );

        $this->assertTrue($this->db->existsRegisterId(1, $driverPaymentTable));
        $this->assertTrue($this->db->existsRegisterId(2, $driverPaymentTable));
        $this->assertTrue($this->db->existsRegisterId(3, $driverPaymentTable));
    }
}
