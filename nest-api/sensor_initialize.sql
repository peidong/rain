CREATE DATABASE IF NOT EXISTS nest;
USE nest;
DROP TABLE IF EXISTS nest.thermostat;
CREATE TABLE IF NOT EXISTS nest.thermostat (
    `id` int(11) NOT NULL DEFAULT 0,
    `current_temperature` int(11) DEFAULT NULL,
    `humidity` int(11) DEFAULT NULL,
    `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
    ) ENGINE = InnoDB;

INSERT INTO `thermostat` VALUES (1, 75, 65,'0000-00-00 00:00:00');
