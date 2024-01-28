<?php

declare(strict_types=1);

namespace Tests;

use Tests\Tables\Drivers;
use Tests\Tables\DriversPayment;

class EntityCloneSeedTest extends EntityCloneTestCommons
{
    public function testEntityCloneDriversTable()
    {
        $this->db->renewTable('source', ($driversTable = new Drivers()));
        $this->db->seed('source', $driversTable);
        $this->assertSame(
            1,
            $this->db->countEntries('source', $driversTable)
        );
    }

    public function testEntityClonePaymentTable()
    {
        $this->db->renewTable('source', ($driverPaymentTable = new DriversPayment()));
        $this->db->seed('source', $driverPaymentTable);
        $this->assertSame(
            3,
            $this->db->countEntries('source', $driverPaymentTable)
        );
    }
}
