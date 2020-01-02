<?php 
//Include Config & Libs
include("../config.php");

//Connect to Database
$conn = new mysqli($dbconfig["server"], 
$dbconfig["username"],
$dbconfig["password"],
$dbconfig["database"]);

//Set table names
$tables = array();
$tables["config"] = $dbconfig["prefix"] . "conf";
$tables["content"] = $dbconfig["prefix"] . "content";
$tables["media"] = $dbconfig["prefix"] . "media";
$tables["templates"] = $dbconfig["prefix"] . "templates";
$tables["users"] = $dbconfig["prefix"] . "users";


//Create statements
$createStatements = array();
$createStatements["config"] = 'CREATE TABLE `' . $tables["config"] .'` (
	`conf_name` VARCHAR(255) NOT NULL,
	`conf_val` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`conf_name`))';

$createStatements["content"] = 'CREATE TABLE `'. $tables["content"] .'` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`url` VARCHAR(255) NOT NULL,
	`title` TEXT NOT NULL,
	`subtitle` TEXT NOT NULL,
	`content_html` TEXT NOT NULL,
	`image` INT NOT NULL,
	`created` INT NOT NULL,
	`published` TINYINT(2) NOT NULL,
	`static` TINYINT(2) NOT NULL,
	`showdate` TINYINT(2) NOT NULL,
	PRIMARY KEY (`id`,`url`)
)';

$createStatements["media"] = 'CREATE TABLE `'. $tables["media"] .'` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`path` TEXT NOT NULL,
	`desc` TEXT NOT NULL,
	PRIMARY KEY (`id`)
)';

$createStatements["templates"] = 'CREATE TABLE `'. $tables["templates"] .'` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255),
	PRIMARY KEY (`id`)
)';

$createStatements["users"] = 'CREATE TABLE `' . $tables["users"] . '` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`pass_hash` TEXT NOT NULL,
	`author_name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`,`name`)
)';
//Query statemens
foreach($createStatements as $key => $statement){
    if(!$conn->query($statement)){
        die("Could not create table: " . $key . ". Please contact an administrator.");
    }
}