<?php
require_once '../vendor/autoload.php';
include '../config/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Debug variables
$debug_info = [];
$is_member = false;

try {
    // Check if JWT token exists
    if (isset($_COOKIE['jwt_token'])) {
        $jwt = $_COOKIE['jwt_token'];
        $debug_info[] = "JWT token found in cookie";
        
        // Make sure $secretKey is defined
        if (!isset($secretKey)) {
            $debug_info[] = "ERROR: Secret key is not defined";
        } else {
            // Try to decode the JWT
            try {
                $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
                $debug_info[] = "JWT successfully decoded";
                if ($decoded->iss !== 'yourdomain.com' || $decoded->aud !== 'yourdomain.com') {
                    // Log the invalid token attempt
                    $debug_info[] = "ERROR: Invalid token issuer or audience";
                    throw new Exception('Invalid token issuer or audience');
                }
                // Get user_id from the JWT
                if (isset($decoded->sub)) {
                    $user_id = $decoded->sub;
                    $debug_info[] = "User ID from JWT: " . $user_id;
                    
                    // Check if database connection exists
                    if (!isset($conn)) {
                        $debug_info[] = "ERROR: Database connection not available";
                    } else {
                        // Query to check if user is a member
                        $query = "SELECT * FROM members WHERE member_id = ?";
                        $stmt = mysqli_prepare($conn, $query);
                        
                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, "i", $user_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            
                            // Check if user is a member
                            $is_member = mysqli_num_rows($result) > 0;
                            $debug_info[] = "Query executed. User is " . ($is_member ? "a member" : "not a member");
                            
                            // If user is a member, show member details
                            if ($is_member) {
                                $member_data = mysqli_fetch_assoc($result);
                                $debug_info[] = "Member data: " . print_r($member_data, true);
                            }
                        } else {
                            $debug_info[] = "ERROR: Failed to prepare statement: " . mysqli_error($conn);
                        }
                    }
                } else {
                    $debug_info[] = "ERROR: No user_id found in JWT payload";
                }
            } catch (Exception $e) {
                $debug_info[] = "ERROR: Failed to decode JWT: " . $e->getMessage();
            }
        }
    } else {
        $debug_info[] = "No JWT token found in cookie";
    }
} catch (Exception $e) {
    $debug_info[] = "CRITICAL ERROR: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>JWT Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .debug-container { 
            background-color: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            border-left: 5px solid #007bff;
        }
        .debug-item { margin-bottom: 10px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>JWT Debugging Information</h1>
    
    <div class="debug-container">
        <h2>Debug Log:</h2>
        <?php foreach ($debug_info as $info): ?>
            <div class="debug-item <?php echo (strpos($info, 'ERROR') !== false) ? 'error' : ''; ?>
                               <?php echo (strpos($info, 'success') !== false) ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($info); ?>
            </div>
        <?php endforeach; ?>
        
        <h2>JWT Status:</h2>
        <div class="debug-item">
            <strong>Is Member:</strong> <?php echo $is_member ? "Yes" : "No"; ?>
        </div>
    </div>
</body>
</html>
