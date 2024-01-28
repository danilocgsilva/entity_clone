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
    }

    protected function createPdo(?string $databaseName = null)
    {
        $baseStringPdoCreation = "mysql:host=%s;charset=utf8mb4;port=%s";
        
        $stringPdoCreating = sprintf($baseStringPdoCreation, getenv("ENTITYCLONE_DB_HOST"), getenv("ENTITYCLONE_DB_PORT"));

        if ($databaseName) {
            $stringPdoCreating .= ";dbname={$databaseName}";
        }

        return new PDO(
            $stringPdoCreating, 
            getenv("ENTITYCLONE_DB_USER"), 
            getenv("ENTITYCLONE_DB_PASSWORD")
        );
    }
}
