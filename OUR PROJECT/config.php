<?php
/* Database credentials */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); 
// !! UPDATE DATABASE NAME !!
define('DB_NAME', 'VolunteerManagementSystemDb'); 

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false){
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}
?>