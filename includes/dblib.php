<?php 
//Include Config & Libs
include("../config.php");

//Connect to Database
$conn = new mysqli($dbconfig["server"], 
$dbconfig["username"],
$dbconfig["password"],
$dbconfig["database"]);

//Set table names
$configtable = $dbconfig["prefix"] . "conf";
$contenttable = $dbconfig["prefix"] . "content";
$mediatable = $dbconfig["prefix"] . "media";
$templatetable = $dbconfig["prefix"] . "template";


