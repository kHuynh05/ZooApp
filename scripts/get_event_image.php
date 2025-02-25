<?php
// Include database connection
include '../config/database.php';

if (isset($_GET['event_id'])) {
    echo "Event ID received: " . htmlspecialchars($_GET['event_id']);
} else {
    echo "No event ID received.";
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 2;

// Clean any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Get image
$sql = "SELECT picture FROM events WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->bind_result($image_data);
$stmt->execute();
$stmt->store_result();
$stmt->fetch();

if ($stmt->num_rows > 0 && !empty($image_data)) {
    // Disable compression
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
    }
    @ini_set('zlib.output_compression', '0');
    
    // Set proper headers for JPEG
    header("Content-Type: image/jpeg");
    header("Content-Length: " . strlen($image_data));
    header("Cache-Control: no-cache");
    
    // Output the image data
    echo $image_data;
} else {
    header("Content-Type: text/plain");
    echo "No image found for event_id = $event_id";
}

$stmt->close();
$conn->close();
?>
