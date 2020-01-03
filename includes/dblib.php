<?php
//Include Config & Libs
include($_SERVER["DOCUMENT_ROOT"] . "/config.php");

//Connect to Database
$conn = new mysqli(
	$dbconfig["server"],
	$dbconfig["username"],
	$dbconfig["password"],
	$dbconfig["database"]
);
$conn->set_charset("utf8_bin");

//Set table names
$tables = array();
$tables["config"] = $dbconfig["prefix"] . "conf";
$tables["content"] = $dbconfig["prefix"] . "content";
$tables["media"] = $dbconfig["prefix"] . "media";
$tables["templates"] = $dbconfig["prefix"] . "templates";
$tables["users"] = $dbconfig["prefix"] . "users";


//Create statements
$createStatements = array();
$createStatements["config"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["config"] . '` (
	`conf_name` VARCHAR(255) NOT NULL,
	`conf_val` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`conf_name`))';

$createStatements["content"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["content"] . '` (
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

$createStatements["media"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["media"] . '` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`path` TEXT NOT NULL,
	`desc` TEXT NOT NULL,
	PRIMARY KEY (`id`)
)';

$createStatements["templates"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["templates"] . '` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255),
	PRIMARY KEY (`id`)
)';

$createStatements["users"] = 'CREATE TABLE IF NOT EXISTS `' . $tables["users"] . '` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`role` INT NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`pass_hash` TEXT NOT NULL,
	`author_name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`,`name`)
)';
//Query statemens
foreach ($createStatements as $key => $statement) {
	if (!$conn->query($statement)) {
		die("Could not create table: " . $key . ". Please contact an administrator. <br/> Error: " . $conn->error);
	}
}

//functions for config DB
/**
 * Function Checks, if Config Exists in Database
 * @param string $conf_name name of the config entry
 * @return bool Config exists or not
 */
function confExists(string $conf_name)
{
	global $tables;
	global $conn;
	$table = $tables["config"];
	$q = "SELECT * FROM `" . $table . "` WHERE `conf_name` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $conf_name);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();
	if ($result->num_rows == 1) {
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
function getConfData(string $conf_name)
{
	global $tables;
	global $conn;
	$table = $tables["config"];
	$q = "SELECT * FROM `" . $table . "` WHERE `conf_name` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $conf_name);
	$result = $stmt->get_result();
	$stmt->close();
	if ($result->num_rows == 1) {
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
function getAllConfigData()
{
	global $tables;
	global $conn;
	$table = $tables["config"];
	$q = "SELECT * FROM '" . $table . "'";
	$result = $conn->query($q);
	$datalist = array();
	while ($confData = $result->fetch_assoc()) {
		array_push($datalist, $confData);
	}
	return $datalist;
}
/**
 * Sets config-value in the database
 * @param string $conf_name name of the config-entry
 * @param string $conf_data data that should be set
 * @return array returns the data as array. Keys: "conf_name", "conf_val"
 */
function setConfigData(string $conf_name, string $conf_data)
{
	global $tables;
	global $conn;
	$table = $tables["config"];
	if (confExists($conf_name)) {
		$q = "UPDATE `" . $table . "` SET `conf_val` = ? WHERE `conf_name` = ?";
		$stmt = $conn->prepare($q);
		$stmt->bind_param("ss", $conf_name, $conf_name);
	} else {
		$q = "INSERT INTO `" . $table . "` (`conf_name`, `conf_val`) VALUES (?,?)";
		$stmt = $conn->prepare($q);
		$stmt->bind_param("ss", $conf_name, $conf_data);
	}
	try {
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e) {
		die("Could not update or insert Config-Data. <br/> Error: " . $conn->error);
	}
}

/**
 * Deletes config entry from Database
 * @param string $conf_name the name of the config entry
 * @return bool return true if config has been deleted
 */
function removeConfigData(string $conf_name)
{
	global $tables;
	global $conn;
	$table = $tables["config"];
	if (confExists($conf_name)) {
		$q = "DELETE FROM `" . $table . "` WHERE `conf_name` = ?";
		$stmt = $conn->prepare($q);
		$stmt->bind_param("s", $conf_name);
		try {
			$stmt->execute();
			$stmt->close();
		} catch (Exception $e) {
			die("Could not remove Config-Data. <br/> Error: " . $conn->error);
		}
	}
	return true;
}

// Functions for User Data-Management
/**
 * Checks if User Exists
 * @param int $id The ID of the User
 * @return bool true if users exists, false if not
 */
function userExists(int $id)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if ($result->num_rows == 1) {
			return true;
		} else {
			return false;
		}
	} catch (Exception $e) {
		die("Could not check if user exists. <br/> Error: " . $conn->error);
	}
}
/**
 * Gets the ID of an user
 * @param string $name The name of the user
 * @return int returns the ID, null if user doesn't exist
 */
function getUserID(string $name)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `" . $table . "` WHERE `name` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $name);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if ($result->num_rows == 1) {
			$data = $result->fetch_assoc();
			return $data["id"];
		} else {
			return null;
		}
	} catch (Exception $e) {
		die("Could not get userID. <br/> Error: " . $conn->error);
	}
}

/**
 * Gets the Data for a user by ID
 * @param int $id The ID of the user
 * @return array returns array with data from database or null, if user doesn't exist
 */
function getUserData(int $id)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if ($result->num_rows == 1) {
			$data = $result->fetch_assoc();
			return $data;
		} else {
			return null;
		}
	} catch (Exception $e) {
		die("Could not get user-data. <br/> Error: " . $conn->error);
	}
}
function getAllUserData()
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "SELECT * FROM `" . $table . "`";
	$result = $conn->query($q);
	$datalist = array();
	while ($userData = $result->fetch_assoc()) {
		array_push($datalist, $userData);
	}
	return $datalist;
}
/**
 * Sets the data for an user to the database
 * @param int $id The ID for the user
 * @param int $role the new role
 * @param string $name the new name
 * @param string $pass_hash the new, hashed password
 * @param string $author_name the new author-name
 * @param string $email the new e-mail adress
 * @return array returns the data, that has been written to the DB
 */
function updateUserData(int $id, int $role, string $name, string $pass_hash, string $author_name, string $email)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	if (userExists($id)) {
		$q = "UPDATE `" . $table . "` SET `role` = ?,`name` = ?', `pass_hash` = ?, `author_name` = ? `email` = ? WHERE `id` = ?";
		$stmt = $conn->prepare($q);
		$stmt->bind_param("issssi", $role, $name, $pass_hash, $author_name, $email, $id);
		try {
			$stmt->execute();
			$stmt->close();
			return getUserData($id);
		} catch (Exception $e) {
			die("Could not update user-data. <br/> Error: " . $conn->error);
		}
	} else {
		die("Trying to update an non-existing user.");
	}
}
/**
 * Creates an new user in the Database
 * @param int $role roleID
 * @param string $name a unique name for the user
 * @param string $pass_hash the hashed password
 * @param string $author_name the name that should be displayed in posts
 * @param string $email the users e-mail adress
 * @return array the data that has been written to the DB
 */
function addUserData(int $role, string $name, string $pass_hash, string $author_name, string $email)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	if (getUserID($name) == null) {
		$q = "INSERT INTO `" . $table . "`(`role`,`name`,`pass_hash`,`author_name`,`email`) VALUES (?,?,?,?,?)";
		$stmt = $conn->prepare($q);
		$stmt->bind_param("issss", $role, $name, $pass_hash, $author_name, $email);
		try {
			$stmt->execute();
			$stmt->close();
			return getUserData(getUserID($name));
		} catch (Exception $e) {
			die("Error creating user-data. <br/> Error: " . $conn->error);
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
function removeUserData(int $id)
{
	global $tables;
	global $conn;
	$table = $tables["users"];
	$q = "DELETE FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try {
		$stmt->execute();
		$stmt->close();
		return true;
	} catch (Exception $e) {
		die("Error deleting user-data. <br/> Error: " . $conn->error);
	}
}

//Functions for Content-DB Management
/**
 * Get Content-Data as array
 * @param int $id The Id of the content
 * @return array array with the content data
 */
function getCotentData(int $id)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		$data = $result->fetch_assoc();

		if ($data["published"] == 1) {
			$data["published"] = true;
		} else {
			$data["published"] = false;
		}

		if ($data["static"] == 1) {
			$data["static"] = true;
		} else {
			$data["static"] = false;
		}

		if ($data["showdate"] == 1) {
			$data["showdate"] = true;
		} else {
			$data["showdate"] = false;
		}
		return $data;
	} catch (Exception $e) {
		die("Error getting content. <br/> Error: " . $conn->error);
	}
}
/**
 * Gets the data for all content in DB
 * @return array array containing the normal content-arrays
 */
function getAllContentData()
{
	global $tables;
	global $conn;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "`";
	$stmt = $conn->prepare($q);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		$compdata = array();
		while ($data = $result->fetch_assoc()) {
			if ($data["published"] == 1) {
				$data["published"] = true;
			} else {
				$data["published"] = false;
			}

			if ($data["static"] == 1) {
				$data["static"] = true;
			} else {
				$data["static"] = false;
			}

			if ($data["showdate"] == 1) {
				$data["showdate"] = true;
			} else {
				$data["showdate"] = false;
			}
			array_push($compdata, $data);
		}
		return $compdata;
	} catch (Exception $e) {
		die("Error getting all content-data. <br/> Error: " . $conn->error);
	}
}
/**
 * 
 */
function addContentData(string $url, string $title, string $subtitle, string $content_html,
int $image, int $created, bool $published, bool $static, bool $showdate)
{
	//TODO set Content Data
}
function updateContentData()
{
}
function removeContentData()
{
}
function getCotentIdByURL()
{
}
