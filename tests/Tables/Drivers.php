<?php

declare(strict_types=1);

namespace Tests\Tables;

class Drivers
{
    public const TABLE_NAME = "drivers";
    public const CREATE = "CREATE TABLE `" . self::TABLE_NAME . "` (" .
    "    `id` INT NOT NULL AUTO_INCREMENT, " .
    "    `name` VARCHAR(192) NOT NULL, " . 
    "    `age` TINYINT NOT NULL, " .
    "    PRIMARY KEY (`id`)" .
    ") ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;";

    public const INSERT = "INSERT INTO `drivers` (id, name, age)" .
    " VALUES " .
    "(6, 'Tobias Silva', 33);";
}

