<?php

declare(strict_types=1);

namespace Tests;

use Tests\Tables\Drivers;
use Tests\Tables\DriversPayment;

class EntityCloneSeedTest extends EntityCloneTestCommons
{
    public function testEntityCloneDriversTable()
    {
        $this->db->setDatabase('source');
        $this->db->renewTable('source', ($driversTable = new Drivers()));

        $this->db->seed($driversTable);
        $this->assertSame(
            1,
            $this->db->countEntries('source', $driversTable)
        );
    }

    public function testEntityCloneDefaultDriversTable()
    {
        $this->db->setDatabase('source');
        $this->db->renewTable('source', ($driversTable = new Drivers()));
        $driversTable->seedDefaultDriver('source', $this->db->getPdo());
        $this->assertSame(
            1,
            $this->db->countEntries('source', $driversTable)
        );
    }

    public function testEntityClonePaymentTable()
    {
        $this->db->setDatabase('source');
        $this->db->renewTable('source', ($driverPaymentTable = new DriversPayment()));
        $this->db->seed($driverPaymentTable);
        $this->assertSame(
            3,
            $this->db->countEntries('source', $driverPaymentTable)
        );
    }

    public function testEntityClonePaymentDefaultFromTable()
    {
        $driverPaymentTable = new DriversPayment();
        $this->db->setDatabase('source');
        $this->db->renewTable('source', ($driverPaymentTable = new DriversPayment()));
        $driverPaymentTable->seedDefaultPayments('source', $this->db->getPdo());
        $this->assertSame(
            3,
            $this->db->countEntries('source', $driverPaymentTable)
        );
    }

    public function testAdditionalDriverSeed()
    {
        $driversTable = new Drivers();
        $this->db->setDatabase('source');
        $this->db->renewTable('source', $driversTable);

        $driversTable->seedDefaultDriver('source', $this->db->getPdo());
        $driversTable->seedAdditionalDriver('source', $this->db->getPdo());

        $this->assertSame(
            2,
            $this->db->countEntries('source', $driversTable)
        );
    }

    public function testAdditionalDriverPaymentSeed()
    {
        $driverPaymentTable = new DriversPayment();

        $this->db->setDatabase('source');
        $this->db->renewTable('source', $driverPaymentTable);
        $driverPaymentTable->seedAdditionalDriverPayments('source', $this->db->getPdo());

        $this->assertSame(
            4,
            $this->db->countEntries('source', $driverPaymentTable)
        );
    }
}
