<?php
session_start();
$_SESSION = array(); // Unset all of the session variables
session_destroy(); // Destroy the session.
header("location: login.php");
exit;
?>