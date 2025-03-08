<head>
    <link rel="stylesheet" href="../assets/css/homepage.css">
</head>
<?php
// Include database connection
include '../config/database.php';  // Make sure the path is correct

// Fetch the upcoming events (you can adjust the query based on your database)
$sql = "SELECT animal_id, animal_name, enclosure_id, FROM animals";

$result = $conn->query($sql);

// Check if there are any events in the result
if ($result->num_rows > 0) {
    // Loop through the results and display each event
    $animals = [];
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
} else {
    // Handle the case if no events are found
    $animals = [];
}

// Close the connection
$conn->close();
?>