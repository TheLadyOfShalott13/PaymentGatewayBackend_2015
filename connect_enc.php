<?php
$host = 'localhost:81';
$user = 'root';
$pass = '';
$database = 'sample';

$conn = mysql_connect($host, $user, $pass); //opens a database connection and returns a database link
mysql_select_db($database); //selects the database, returns true or false on behaviour
?>