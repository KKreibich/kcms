<?php
//Include Config & Libs
require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");

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
	`type` INT NOT NULL,
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
	$q = "SELECT * FROM `" . $table . "`";
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
		die("Error getting content-data. <br/> Error: " . $conn->error);
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
 * Adds the given content-data to the database
 * @param string $url the end of the url for the page
 * @param string $title the title of the page
 * @param string $subtitle the subtitle of the page
 * @param string $content_html the content of the page (html support)
 * @param int $image the mediaID of the image
 * @param int $created timestamp, when the page was created / published
 * @param bool $published published
 * @param bool $static static
 * @param bool $showdate show date
 */
function addContentData(
	string $url,
	string $title,
	string $subtitle,
	string $content_html,
	int $image,
	int $created,
	bool $published,
	bool $static,
	bool $showdate
) {
	global $conn;
	global $tables;
	$table = $tables["content"];

	$q = "INSERT INTO `" . $table . "`
	(`url`,`title`,`subtitle`,`content_html`,`image`,
	`created`,`published`,`static`,`showdate`) VALUES (?,?,?,?,?,?,?,?,?)";
	$stmt = $conn->prepare($q);

	if ($published) {
		$published_db = 1;
	} else {
		$published_db = 0;
	}
	if ($static) {
		$static_db = 1;
	} else {
		$static_db = 0;
	}
	if ($showdate) {
		$showdate_db = 1;
	} else {
		$showdate_db = 0;
	}

	$stmt->bind_param(
		"ssssiiiii",
		$url,
		$title,
		$subtitle,
		$content_html,
		$image,
		$created,
		$published_db,
		$static_db,
		$showdate_db
	);
	try {
		$stmt->execute();
		$stmt->close();
		return true;
	} catch (Exception $e) {
		die("Could not write content-data to database. <br/> Error: " . $conn->error);
	}
}
/**
 * Updates the content-data
 * @param int $id The ID of the content object
 * @param string $url the end of the url for the page
 * @param string $title the title of the page
 * @param string $subtitle the subtitle of the page
 * @param string $content_html the content of the page (html support)
 * @param int $image the mediaID of the image
 * @param int $created timestamp, when the page was created / published
 * @param bool $published published
 * @param bool $static static
 * @param bool $showdate show date
 */
function updateContentData(
	int $id,
	string $url,
	string $title,
	string $subtitle,
	string $content_html,
	int $image,
	int $created,
	bool $published,
	bool $static,
	bool $showdate
) {
	global $conn;
	global $tables;
	$table = $tables["content"];

	$q = "UPDATE `" . $table . "`
	 SET `url` = ?,`title` = ?,`subtitle` = ?,`content_html` = ?,`image` = ?,
	`created` = ?,`published` = ?,`static` = ?,`showdate` = ? WHERE `id` = ?";
	$stmt = $conn->prepare($q);

	if ($published) {
		$published_db = 1;
	} else {
		$published_db = 0;
	}
	if ($static) {
		$static_db = 1;
	} else {
		$static_db = 0;
	}
	if ($showdate) {
		$showdate_db = 1;
	} else {
		$showdate_db = 0;
	}

	$stmt->bind_param(
		"ssssiiiiii",
		$url,
		$title,
		$subtitle,
		$content_html,
		$image,
		$created,
		$published_db,
		$static_db,
		$showdate_db,
		$id
	);
	try {
		$stmt->execute();
		$stmt->close();
		return true;
	} catch (Exception $e) {
		die("Could not update content-data in database. <br/> Error: " . $conn->error);
	}
}
/**
 * Removes the data of a content object from the Database
 * @param string $id The ID of the content object
 */
function removeContentData(int $id)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "DELETE FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try {
		$stmt->execute();
		$stmt->close();
		return true;
	} catch (Exception $e) {
		die("Could not delete content-data from database. <br/> Error: " . $conn->error);
	}
}
/**
 * Gets content-data by url
 * @param string $url the url
 * @return array returns array with cotent-data
 */
function getContentDataByURL(string $url)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "` WHERE `url` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $url);
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
 * Gets content-data by url
 * @param string $url the url
 * @return array returns array with cotent-data
 */
function getContentDataByID(int $id)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "` WHERE `url` = ?";
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
 * Checks, if content exists by URL
 * @param string $url The URL to check
 * @return bool true if exists, false if not
 */
function contentExistsByURL(string $url)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "` WHERE `url` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $url);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e) {
		die("Could not check if content-data exists. <br/> Error: " . $conn->error);
	}
}
/**
 * Checks if content exists by ID
 * @param int $id The ID to check
 * @return bool true if exists, false if not
 */
function contentExistsByID(int $id)
{
	global $conn;
	global $tables;
	$table = $tables["content"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $url);
	try {
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e) {
		die("Could not check if content-data exists. <br/> Error: " . $conn->error);
	}
}

//! Functions for Media-Management
/**
 * Adds the data for a media-object to the database
 * @param string $name Name
 * @param int $type ID for the Media-Type
 * @param string $path Path to the media-file
 * @param string $desc Description
 */
function addMediaData(string $name, int $type, string $path, string $desc)
{
	global $conn;
	global $tables;
	$table = $tables["media"];
	$q = "INSERT INTO `" . $table . "`(`name`,`type`,`path`,`desc`) VALUES (?,?,?,?)";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("siss", $name, $type, $path, $desc);
	try {
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e) {
		die("Could not insert Media-Data. <br/> Error: " . $conn->error);
	}
}
/**
 * Updates the data for a media-object in the database
 * @param int $id The ID of the object to update
 * @param string $name Name
 * @param int $type ID for the Media-Type
 * @param string $path Path to the media-file
 * @param string $desc Description
 */
function updateMediaData(int $id, string $name, int $type, string $path, string $desc)
{
	global $tables;
	global $conn;
	$table = $tables["media"];
	$q = "UPDATE `" . $table . "` SET `name` = ?, `type` = ?, `path` = ?, `desc` = ? WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("sissi", $name, $type, $path, $desc, $id);
	try {
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e) {
		die("Could not update Media-Data. <br/> Error: " . $conn->error);
	}
}
/**
 * Fetches the data for a media-object from the database
 * @param int $id The ID of the object
 * @return array Array with data, returns null if not existing
 */
function getMediaData(int $id){
	global $tables;
	global $conn;
	$table = $tables["media"];
	$q = "SELECT * FROM `". $table ."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows != 1){
			return null;
		}
		$data = $result->fetch_assoc();
		$stmt->close();
		return $data;

	} catch (Exception $e){
		die("Could not fetch Media-Data. <br/> Error: " . $conn->error);
	}

}
/**
 * Checks if media-object exists in Database
 * @param int $id The ID to check
 * @return bool exists / not
 */
function mediaDataExists(int $id){
	global $tables;
	global $conn;
	$table = $tables["media"];
	$q = "SELECT * FROM `". $table ."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;

	} catch (Exception $e){
		die("Could not fetch Media-Data. <br/> Error: " . $conn->error);
	}
}
/**
 * Deletes the data for the media-object
 * @param int $id the ID of the item to delete
 */
function deleteMediaData(int $id){
	global $tables;
	global $conn;
	$table = $tables["media"];
	$q = "DELETE FROM `". $table ."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$stmt->close();

	} catch (Exception $e){
		die("Could not fetch Media-Data. <br/> Error: " . $conn->error);
	}
}

/**
 * Fetches all media-data from the database.
 * @return array Array containing normal data-arrays
 */
function getAllMediaData()
{
	global $tables;
	global $conn;
	$table = $tables["media"];
	$q = "SELECT * FROM `" . $table . "`";
	$result = $conn->query($q);
	$datalist = array();
	while ($confData = $result->fetch_assoc()) {
		array_push($datalist, $confData);
	}
	return $datalist;
}

//! Functions for template-management
//INFO: Only for having a list of all templates,
// all data will be read from the template-config.
/**
 * Adds data for a template to the database. 
 * @param string $name Not user-readable name to identify folder of the template
 */
function addTemplateData(string $name){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "INSERT INTO `". $table ."`(`name`) VALUES (?)";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $name);
	try{
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e){
		die("Error writing template-data to database.");
	}
}
/**
 * Checks if template exits by name
 * @param string $name The name to check
 * @return int If exists, returns ID, else returns null
 */
function templateExistsByName(string $name){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "SELECT * FROM `" . $table . "` WHERE `name` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $name);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e){
		die("Could not check if template exists by name.");
	}
}
/**
 * Checks if template exists by ID
 * @param int $id The ID to check
 * @return string If exists, returns name, else returns null
 */
function templateExistsByID(int $id){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e){
		die("Could not check if template exists by ID.");
	}
}
/**
 * Gets the data for a template
 * @param int $id The ID of the template
 * @return array Array with the data from the database, nulll if not existing..
 */
function getTemplateData(int $id){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "SELECT * FROM `" . $table . "` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return null;
	} catch (Exception $e){
		die("Could not check if template exists by ID.");
	}
}
/**
 * Gets all template-data from the database
 * @return array Array with normal data-arrays for templats
 */
function getAllTemplateData(){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "SELECT * FROM `".$table."`";
	$stmt = $conn->prepare($q);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		$ret_list = array();
		while($obj = $result->fetch_assoc()){
			array_push($ret_list, $obj);
		}
		return $ret_list;
	} catch (Exception $e){
		die("Could not fetch all template-data.");
	}
}

/**
 * Deletes the data for a template from the DB
 * @param int $id The ID of the object to delete
 */
function deleteTemplateData(int $id){
	global $tables;
	global $conn;
	$table = $tables["templates"];
	$q = "DELETE FROM `".$table."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e){
		die("Could not delete template-data from database.");
	}
}
//! Functions for route-management
/**
 * Adds route-data to the database
 * @param string $name The display-name in admin-panel
 * @param string $url the url to route
 * @param int $type the route-type (0 = Redirect, 1 = show page)
 * @param int $z_code HTTP-Code to use for redirect, set null if type != 0
 * @param string $z_url The URL to redirect to, set null if type != 0
 * @param int $o_pgid The Page-ID of the page to show, set null if type != 1
 */
function addRouteData(string $name, string $url, int $type, int $z_code, string $z_url, int $o_pgid){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "INSERT INTO `". $table ."`(`name`, `url`, `type`, `0_code`, `0_url`, `1_pgid`) VALUES (?,?,?,?,?,?)";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("ssiisi", $name, $url, $type, $z_code, $z_url, $o_pgid);
	try{
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e){
		die("Could not add route-data.");
	}
}

/**
 * Checks if route exists by id
 * @param int $id the id to check
 * @return bool exists / not
 */
function routeExistsByID(int $id){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "SELECT * FROM `".$table."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e){
		die("Could not check if route exists by ID.");
	}
}

/**
 * Checks if route exists by url
 * @param string $url the url to check
 * @return bool exists / not
 */
function routeExistsByURL(string $url){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "SELECT * FROM `".$table."` WHERE `url` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $url);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result->num_rows == 1;
	} catch (Exception $e){
		die("Could not check if route exists by ID.");
	}
}

/**
 * gets the route-id for a url
 * @param string $url 
 * @return int the ID, null if not existing
 */
function getRouteID(string $url){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "SELECT * FROM `".$table."` WHERE `url` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("s", $url);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if($result->num_rows != 1){
			return null;
		}
		return $result->fetch_assoc()["id"];
	} catch (Exception $e){
		die("Could not get route-ID.");
	}
}

/**
 * Fetches the Data for a route from the database
 * @param int $id the id of the route
 * @return array Array with the route-data
 */
function getRouteData(int $id){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "SELECT * FROM `".$table."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i", $id);
	try{
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		if($result->num_rows != 1){
			return null;
		}
		return $result->fetch_assoc();
	} catch (Exception $e){
		die("Could not get route-data.");
	}
}

/**
 * Updates the data for a route
 * @param int $id The ID of the object to update
 * @param string $name The display-name in admin-panel
 * @param string $url the url to route
 * @param int $type the route-type (0 = Redirect, 1 = show page)
 * @param int $z_code HTTP-Code to use for redirect, set null if type != 0
 * @param string $z_url The URL to redirect to, set null if type != 0
 * @param int $o_pgid The Page-ID of the page to show, set null if type != 1
 */
function updateRouteData(int $id, string $name, string $url, int $type, int $z_code, string $z_url, int $o_pgid){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "UPDATE `". $table ."` SET 
	`name` = ?, `url` = ?, `type` = ?, `0_code`, `0_url`, `1_pgid`
	WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("ssiisii", $name, $url, $type, $z_code, $z_url, $o_pgid, $id);
	try{
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e){
		die("Could not update route-data.");
	}
}

/**
 * Deletes the data for a route
 * @param int $id The ID of the route to delete
 */
function deleteRouteData(int $id){
	global $conn;
	global $tables;
	$table = $tables["routes"];
	$q = "DELETE FROM `". $table ."` WHERE `id` = ?";
	$stmt = $conn->prepare($q);
	$stmt->bind_param("i",$id);
	try{
		$stmt->execute();
		$stmt->close();
	} catch (Exception $e){
		die("Could not update route-data.");
	}
}