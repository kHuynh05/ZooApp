<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$secret_key = "your_secret_key";
$data = json_decode(file_get_contents("php://input"));

if ($data->email === "user@example.com" && $data->password === "password123") {
    $payload = [
        "email" => $data->email,
        "exp" => time() + 3600 // Token expires in 1 hour
    ];
    $jwt = JWT::encode($payload, $secret_key, 'HS256');
    echo json_encode(["token" => $jwt]);
} else {
    echo json_encode(["message" => "Invalid login"]);
}
?>
