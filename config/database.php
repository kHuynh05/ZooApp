<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure session is started only once
}
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Validate required variables
$dotenv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'SECRET_KEY'
])->notEmpty();

// Get database credentials using $_ENV
$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$secretKey = $_ENV['SECRET_KEY'];

$conn = mysqli_init();
mysqli_ssl_set($conn,NULL,NULL, __DIR__ . "/../assets/DigiCertGlobalRootCA.crt.pem", NULL, NULL);
mysqli_real_connect($conn, $dbHost, $dbUser, $dbPass, $dbName, 3306);
if (mysqli_connect_errno()) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}else{
    error_log("Connected!");
}
?>
