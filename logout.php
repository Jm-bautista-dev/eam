<?php
session_start();
session_unset();
session_destroy();

// Prevent back button after logout
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jul 2000 00:00:00 GMT");
header("Location: login.php");
exit();
?>
