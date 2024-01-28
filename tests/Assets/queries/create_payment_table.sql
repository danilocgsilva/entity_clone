CREATE TABLE payment (
    `payment_id` INT NOT NULL AUTO_INCREMENT, 
    `driver_id` INT NOT NULL,  
    `amount` FLOAT NOT NULL, 
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;