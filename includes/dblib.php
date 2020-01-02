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
        die("Could not create table: " . $key . ". Please contact an administrator. <br/> Error: " . $conn->error);
    }
}

//functions for config DB
/**
 * Function Checks, if Config Exists in Database
 * @param string $conf_name name of the config entry
 * @return bool Config exists or not
 */
function confExists(string $conf_name){
	global $tables;
	global $conn;
	$q = 'SELECT * FROM `' . $tables["config"] . '` WHERE `conf_name` = `'. $conf_name .'`';
	$result = $conn->query($q);
	if($result->num_rows == 1){
		return true;
	} else {
		return false;
	}
}
/**
 * Gets Config-Data from the Database
 * @param string $conf_name name of the config entry
 * @return array Returns array with data, keys: "conf_name", "conf_val"
 */
function getConfData(string $conf_name){
	global $tables;
	global $conn;
	$q = 'SELECT * FROM `' . $tables["config"] . '` WHERE `conf_name` = `'. $conf_name .'`';
	$result = $conn->query($q);
	if($result->num_rows == 1){
		$data = $result->fetch_assoc();
		return $data;
	} else {
		return null;
	}
}
/**
 * Gets all config-data from the Database
 * @return array Returns array. Array contains arrays with config-data. keys: "conf_name", "conf_val"
 */
function getAllConfigData(){
	global $tables;
	global $conn;
	$q = 'SELECT * FROM ` '. $tables["config"] .' `';
	$result = $conn->query($q);
	if($result->num_rows > 0){
		$configs = array();
		while($data = $result->fetch_assoc()){
			array_push($configs, $data);
		}
		return $configs;
	} else {
		return null;
	}
}
/**
 * Sets config-value in the database
 * @param string $conf_name name of the config-entry
 * @param string $conf_data data that should be set
 * @return array returns the data as array. Keys: "conf_name", "conf_val"
 */
function setConfigData(string $conf_name, string $conf_data){
	global $tables;
	global $conn;
	if(confExists($conf_name)){
		$q = 'UPDATE `'. $tables["config"] .'` SET `conf_val` = `'.$conf_data.'` WHERE `conf_name` = `'.$conf_data.'`';
	} else {
		$q = 'INSERT INTO `'.$tables["config"].'`(conf_name, conf_val) 
		VALUES (`'.$conn->real_escape_string($conf_name).'`,
		`'.$conn->real_escape_string($conf_data).'`)';
	}
	if($conn->query($q)){
		return getConfData($conf_name);
	} else {
		die("Could not set config value. <br/> Error: " . $conn->error);
	}
}