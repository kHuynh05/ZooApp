<?php
include '../config/database.php';
$event_id = 1; // Target event ID
$image_path = "../assets/img/donate-homepage.jpg"; // Change to your image path

// Read the image as binary data
$imageData = file_get_contents($image_path);
if ($imageData === false) {
    die("Error: Could not read image file.");
}

// Update the image in the database
$sql = "UPDATE events SET picture = ? WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("bi", $imageData, $event_id);
$stmt->send_long_data(0, $imageData);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Image successfully stored for event_id = 1!";
} else {
    echo "⚠️ Error: Image was NOT stored.";
}

$stmt->close();
$conn->close();
?>
