<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Assets\Db;
use PDO;

class EntityCloneTest extends TestCase
{
    public function setUp(): void
    {
        $sourcePdo = $this->createPdo();
        $sourcePdo = $this->createPdo();
        $db = new Db();
        $db->migrate($sourcePdo, 'source');
        $db->migrate($sourcePdo, 'destiny');
    }
    
    public function testEntityClone()
    {
        $this->assertTrue(true);
    }

    private function createPdo()
    {
        return new PDO(
            'mysql:host=databasephpapache;charset=utf8mb4', 
            'root', 
            'phppass'
        );
    }
}
