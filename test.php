<?php
require_once  "./includes/dblib.php";

if(!routeExistsByURL("test/test1")){
    addRouteData("testroute", "test/test1", 1,null, null, 12);
}