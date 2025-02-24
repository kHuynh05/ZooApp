
<?php
// Include the database connection
include '../config/database.php';

// Event ID you want to retrieve the image for
$event_id = 2;

// Query the database for the BLOB data
$sql = "SELECT picture FROM events WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($imageData);
$stmt->fetch();

// Output the image
header("Content-Type: image/jpeg");  // Make sure this matches the image type (e.g., image/jpeg, image/png)
echo $imageData;  // Display the binary image data directly

// Close the statement and connection
$stmt->close();
$conn->close();
?>