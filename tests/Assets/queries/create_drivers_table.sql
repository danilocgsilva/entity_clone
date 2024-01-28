CREATE TABLE drivers (
    `driver_id` INT NOT NULL AUTO_INCREMENT, 
    `name` VARCHAR(192) NOT NULL,  
    `age` TINYINT NOT NULL, 
    PRIMARY KEY (`driver_id`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;