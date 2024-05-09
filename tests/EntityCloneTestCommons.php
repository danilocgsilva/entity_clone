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

    /**
     * The PDO object will use environment variables to be created.
     * Optionally, allows enter a database name, so the pdo is created with the default database.
     *
     * @param string|null $databaseName
     * @return PDO
     */
    protected function createPdo(?string $databaseName = null): PDO
    {
        $baseStringPdoCreation = "mysql:host=%s;charset=utf8mb4;port=%s";
        
        $stringPdoCreating = sprintf($baseStringPdoCreation, getenv("ENTITYCLONE_DB_HOST_TEST"), getenv("ENTITYCLONE_DB_PORT_TEST"));

        if ($databaseName) {
            $stringPdoCreating .= ";dbname={$databaseName}";
        }

        return new PDO(
            $stringPdoCreating, 
            getenv("ENTITYCLONE_DB_USER_TEST"), 
            getenv("ENTITYCLONE_DB_PASSWORD_TEST")
        );
    }
}
