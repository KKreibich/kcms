<?php 
//Include Config & Libs
include($_SERVER["DOCUMENT_ROOT"]."/config.php");

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
$createStatements["config"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["config"] .'` (
	`conf_name` VARCHAR(255) NOT NULL,
	`conf_val` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`conf_name`))';

$createStatements["content"] = 'CREATE TABLE IF NOT EXISTS `'. $tables["content"] .'` (
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

$createStatements["media"] = 'CREATE TABLE IF NOT EXISTS `'. $tables["media"] .'` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`path` TEXT NOT NULL,
	`desc` TEXT NOT NULL,
	PRIMARY KEY (`id`)
)';

$createStatements["templates"] = 'CREATE TABLE IF NOT EXISTS `'. $tables["templates"] .'` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255),
	PRIMARY KEY (`id`)
)';

$createStatements["users"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["users"] . '` (
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
	$table = $tables["config"];
	$q = "SELECT * FROM `".$table."` WHERE `conf_name` = '".$conf_name."'";
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
	$table = $tables["config"];
	$q = "SELECT * FROM `".$table."` WHERE `conf_name` = '".$conf_name."'";
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
	$table = $tables["config"];
	$q = "SELECT * FROM '". $table ."'";
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
	$table = $tables["config"];
	if(confExists($conf_name)){
		$q = "UPDATE `".$table."` SET `conf_val` = '". $conn->real_escape_string($conf_data) ."' WHERE `conf_name` = '".$conn->real_escape_string($conf_name)."'";
	} else {
		$q = "INSERT INTO `".$table."` (`conf_name`, `conf_val`) VALUES ('".$conn->real_escape_string($conf_name)."','".$conn->real_escape_string($conf_data)."')";
	}
	if($conn->query($q)){
		return getConfData($conf_name);
	} else {
		die("Could not set config value. <br/> Error: " . $conn->error);
	}
}

/**
 * Deletes config entry from Database
 * @param string $conf_name the name of the config entry
 * @return bool return true if config has been deleted, false if content didn't exist.
 */
function removeConfigData(string $conf_name){
	global $tables;
	global $conn;
	$table = $tables["config"];
	if(confExists($conf_name)){
		$q = "DELETE FROM `".$table."` WHERE `conf_name` = '". $conn->real_escape_string($conf_name) ."'";
		if($conn->query($q)){
			return true;
		} else {
			die("Could not delete config value. <br/> Error: " . $conn->error);
		}
	}
	return false;
}

// Functions for User Data-Management
/**
 * Checks if User Exists
 * @param int $id The ID of the User
 * @return bool true if users exists, false if not
 */
function userExists(int $id){
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `".$table."` WHERE `id` = '".$id."'";
	$result = $conn->query($q);
	if($result->num_rows == 1){
		return true;
	} else {
		return false;
	}
}
/**
 * Gets the ID of an user
 * @param string $name The name of the user
 * @return int returns the ID, null if user doesn't exist
 */
function getUserID(string $name){
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `".$table."` WHERE `name` = '".$name."'";
	$result = $conn->query($q);
	if($result->num_rows == 1){
		$data = $result->fetch_assoc();
		return $data["id"];
	} else {
		return null;
	}
}

/**
 * Gets the Data for a user by ID
 * @param int $id The ID of the user
 * @return array returns array with data from database or null, if user doesn't exist
 */
function getUserData(int $id){
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `".$table."` WHERE `id` = '".$id."'";
	$result = $conn->query($q);
	if($result->num_rows == 1){
		$data = $result->fetch_assoc();
		return $data;
	} else {
		return null;
	}
}
/**
 * Sets the data for an user to the database
 * @param int $id The ID for the user
 * @return array returns the data, that has been written to the DB
 */
function updateUserData(int $id){
	global $tables;
	global $conn;
	$table = $tables["users"];
	if(userExists($id)){
		$q = "";
	} else {
		die("Trying to update an non-existing user.");
	}
}
/**
 * Creates an new user in the Database
 * @param string $name a unique name for the user
 * @param string $pass_hash the hashed password
 * @param string $author_name the name that should be displayed in posts
 * @param string $email the users e-mail adress
 * @return array the data that has been written to the DB
 */
function addUserData(string $name, string $pass_hash, string $author_name, string $email){
	global $tables;
	global $conn;
	$table = $tables["users"];
	if(getUserID($name) == null){
		$q = "INSERT INTO `". $table ."`(`name`,`pass_hash`,`author_name`,`email`) 
		VALUES ('". $conn->real_escape_string($name) ."',
		'". $conn->real_escape_string($pass_hash) ."',
		'". $conn->real_escape_string($author_name) ."',
		'". $conn->real_escape_string($email) ."')";
		if($conn->query($q)){
			return getUserData(getUserID($name));
		} else {
			die("Error creating user. <br/> Error: " . $conn->error);
		}
	} else {
		die("Trying to create user that aready exists.");
	}
}
/**
 * Removes the data for a user from the Database
 * @param int $id The ID of the user
 * @return bool returns true if user has been deleted
 */
function removeUserData(int $id){
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "DELETE FROM `".$table."` WHERE `id` = '".$id."'";
	if($conn->query($q)){
		return true;
	} else {
		die("Could not delete User-Data. <br/> Error: " . $conn->error);
	}
}