<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "This is the index.php file!";
header('Location: /public/homepage.php'); // Adjust folder path if necessary
exit;
?>