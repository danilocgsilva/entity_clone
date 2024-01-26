<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Assets\Db;
use PDO;

class EntityCloneTestCommons extends TestCase
{
    protected Db $db;

    public function setUp(): void
    {
        $pdo = $this->createPdo();
        $this->db = new Db($pdo);

        $this->db->renewDatabase('source');
        $this->db->renewDatabase('destiny');
    }

    protected function createPdo(?string $databaseName = null)
    {
        $baseStringPdoCreation = "mysql:host=%s;charset=utf8mb4;port=%s";
        
        $stringPdoCreating = sprintf($baseStringPdoCreation, getenv("DB_HOST"), getenv("DB_PORT"));

        if ($databaseName) {
            $stringPdoCreating .= ";dbname={$databaseName}";
        }

        return new PDO(
            $stringPdoCreating, 
            getenv("DB_USER"), 
            getenv("DB_PASSWORD")
        );
    }
}
