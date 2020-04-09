<?php
$host  = $_SERVER['HTTP_HOST'];
$extra = 'index.php';
header("Location: http://$host/$extra");
exit;