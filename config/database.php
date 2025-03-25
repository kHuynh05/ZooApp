<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure session is started only once
}

require_once __DIR__ . '/../vendor/autoload.php';

// Get database credentials using getenv() for Azure environment variables
$dbHost = getenv('DB_HOST');  // Get DB_HOST environment variable
$dbName = getenv('DB_NAME');  // Get DB_NAME environment variable
$dbUser = getenv('DB_USER');  // Get DB_USER environment variable
$dbPass = getenv('DB_PASS');  // Get DB_PASS environment variable
$secretKey = getenv('SECRET_KEY');  // Get SECRET_KEY environment variable

// Check if all required environment variables are set
if (!$dbHost || !$dbName || !$dbUser || !$dbPass || !$secretKey) {
    die('Required environment variables are missing!');
}

// SSL connection to MySQL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, __DIR__ . "/../assets/DigiCertGlobalRootCA.crt.pem", NULL, NULL);
mysqli_real_connect($conn, $dbHost, $dbUser, $dbPass, $dbName, 3306);

if (mysqli_connect_errno()) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
} else {
    error_log("Connected!");
}
?>