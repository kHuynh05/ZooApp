<?php
include '../config/database.php';
session_destroy();
header("Location: ../public/homepage.php");
exit();
?>