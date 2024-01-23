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
