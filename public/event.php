<head>
    <link rel="stylesheet" href="../assets/css/event.css">
</head>
<div>
    <?php include('../includes/navbar.php'); ?>
    <?php
    include '../config/database.php';

    if (isset($_GET['event_id'])) {
        $event_id = intval($_GET['event_id']); // Convert to integer for security

        $query = "SELECT event_name, event_date, location, description, picture FROM events WHERE event_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->bind_result($event_name, $event_date, $location, $description, $picture);
        $stmt->fetch();

        if ($event_name) {
            echo '<div class="container">';

            // Display the event image if available
            if (!empty($picture)) {
                echo '<img class="event-img" src="' . $picture . '" alt="Event Image">';
            } else {
                echo "<p>No image available for this event.</p>";
            }

            // Display event details
            echo '<div class="event-info">';
            echo "<h1>Event: " . htmlspecialchars($event_name) . "</h1>";
            echo "<p><strong>Time:</strong> " . htmlspecialchars($event_date) . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>";
            echo "<p>" . htmlspecialchars($description) . "</p>";
            echo '</div>';
            echo '</div>';
        } else {
            echo "<p>Event not found.</p>";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "<p>No event ID provided.</p>";
    }
    ?>
    <?php include('../includes/footer.php'); ?>
</div>