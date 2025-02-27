<?php
include '../config/database.php';

if (!isset($_GET['event_id'])) {
    header("Content-Type: text/plain");
    echo "No event ID received.";
    exit;
}

$event_id = (int)$_GET['event_id'];

// Clean any previous output to avoid corruption
while (ob_get_level()) {
    ob_end_clean();
}

$sql = "SELECT picture FROM events WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($image_data);
    $stmt->fetch();

    if (!empty($image_data)) {
        // Prevent compression & send headers
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');

        header("Content-Type: image/jpeg");
        header("Content-Length: " . strlen($image_data));
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Flush headers before outputting image
        flush();

        // Output the image data
        echo $image_data;
        exit;
    }
}

// If no image found, return a placeholder or text response
header("Content-Type: text/plain");
echo "No image found for event_id = $event_id";

$stmt->close();
$conn->close();
?>
