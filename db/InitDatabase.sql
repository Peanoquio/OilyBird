/* creates the database */
CREATE DATABASE IF NOT EXISTS `testing` CHARACTER SET utf8 COLLATE utf8_general_ci;


/* creates the user and grants privileges */
GRANT ALL ON `testing`.* TO `user`@localhost IDENTIFIED BY '123';
FLUSH PRIVILEGES;


/* create the player table */
CREATE TABLE IF NOT EXISTS `testing`.`player`
(
	`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR(50) NOT NULL DEFAULT '',
	`Age` INT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY ( `Id` )
) ENGINE = `InnoDB`;


/* create the kingdom table */
CREATE TABLE IF NOT EXISTS `testing`.`kingdom`
(
	`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY ( `Id` )
) ENGINE = `InnoDB`;


/* create the general table */
CREATE TABLE IF NOT EXISTS `testing`.`general`
(
	`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR(50) NOT NULL DEFAULT '',
	`Attack` INT UNSIGNED NOT NULL DEFAULT 0,
	`Defense` INT UNSIGNED NOT NULL DEFAULT 0,
	`PlayerId` INT UNSIGNED,
	`KingdomId` INT UNSIGNED,
	PRIMARY KEY ( `Id` ),
	CONSTRAINT `fk_GeneralPlayerId` FOREIGN KEY ( `PlayerId` ) REFERENCES `testing`.`player`( `Id` ) ON DELETE CASCADE,
	CONSTRAINT `fk_GeneralKingdomId` FOREIGN KEY ( `KingdomId` ) REFERENCES `testing`.`kingdom`( `Id` ) ON DELETE CASCADE
) ENGINE = `InnoDB`;