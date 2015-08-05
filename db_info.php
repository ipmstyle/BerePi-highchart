<?php

$mysql_host ='localhost';
$mysql_user = 'root';
$mysql_password='dktkal123';
$mysql_db = 'test';
$conn = mysql_connect($mysql_host, $mysql_user, $mysql_password);
$dbconn = mysql_select_db($mysql_db, $conn);

?>
