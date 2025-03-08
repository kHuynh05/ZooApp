<?php
session_start();
session_destroy();
header("Location: ../public/homepage.php");
exit();
?>