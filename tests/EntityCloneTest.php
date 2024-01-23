<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\EntityClone;
use PHPUnit\Framework\TestCase;
use Tests\Assets\Db;
use PDO;
use Tests\Tables\Drivers;

class EntityCloneTest extends TestCase
{
    private Db $db;

    public function setUp(): void
    {
        $pdo = $this->createPdo();
        $this->db = new Db($pdo);

        $this->db->renewDatabase('source');
        $this->db->renewDatabase('destiny');
    }
    
    public function testEntityClone()
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

    private function createPdo(?string $databaseName = null)
    {
        $stringPdoCreationg = "mysql:host=databasephpapache;charset=utf8mb4";
        if ($databaseName) {
            $stringPdoCreationg .= ";dbname={$databaseName}";
        }
        return new PDO(
            $stringPdoCreationg, 
            'root', 
            'phppass'
        );
    }
}
