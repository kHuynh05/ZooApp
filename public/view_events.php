<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

// Get current datetime
$current_datetime = date('Y-m-d H:i:s');

// Fetch upcoming events
$upcoming_query = "SELECT * FROM events WHERE event_date > ? ORDER BY event_date ASC";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("s", $current_datetime);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

// Fetch past events
$past_query = "SELECT * FROM events WHERE event_date <= ? ORDER BY event_date DESC";
$past_stmt = $conn->prepare($past_query);
$past_stmt->bind_param("s", $current_datetime);
$past_stmt->execute();
$past_result = $past_stmt->get_result();
?>

<div id="eventsPage">
    <head>
    <link rel="stylesheet" href="../assets/css/view_events.css"> 
    </head>
    <h1>Events</h1>
     
    <form id="form" method="POST">
        <div class="form-container">
            <div class="container">
                <section class="upcoming-events">
                    <h2>Upcoming Events</h2>
                    <div class="events-grid">
                        <?php while ($event = $upcoming_result->fetch_assoc()): ?>
                            <a href="event.php?event_id=<?php echo $event['event_id']; ?>" class="event-card">
                                <img src="<?php echo htmlspecialchars($event['picture']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                                <div class="event-info">
                                    <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                    <p class="date"><?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?></p>
                                    <p class="location"><?php echo htmlspecialchars($event['location']); ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>

                <section class="past-events">
                    <h2>Past Events</h2>
                    <div class="events-grid">
                        <?php while ($event = $past_result->fetch_assoc()): ?>
                            <a href="event.php?event_id=<?php echo $event['event_id']; ?>" class="event-card past">
                                <img src="<?php echo htmlspecialchars($event['picture']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                                <div class="event-info">
                                    <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                    <p class="date"><?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?></p>
                                    <p class="location"><?php echo htmlspecialchars($event['location']); ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
