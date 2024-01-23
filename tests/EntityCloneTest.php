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
        $pdo = $this->createPdo();
        $db = new Db($pdo);

        $db->renewDatabase('source');
        $db->renewDatabase('destiny');
    }
    
    public function testEntityClone()
    {
        $this->assertTrue(false);
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
