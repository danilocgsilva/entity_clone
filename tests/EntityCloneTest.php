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
        $db = new Db($sourcePdo);
        if (!$db->databaseExists('source')) {
            $db->migrate('source');
        }
        if (!$db->databaseExists('destiny')) {
            $db->migrate('destiny');
        }
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
