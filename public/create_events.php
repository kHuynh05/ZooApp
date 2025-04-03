<?php
include '../scripts/employeeRole.php';
include '../config/database.php';

$message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and validate form data
        $event_name = trim($_POST['event_name'] ?? '');
        $event_date = trim($_POST['event_date'] ?? '');
        $ending_time = trim($_POST['ending_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validate description length
        if (strlen($description) > 200) {
            throw new Exception("Description must be less than 200 characters");
        }

        // Handle file upload
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $target_dir = "../assets/img/";
            $file_extension = pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            $relative_path = "../assets/img/" . $file_name;

            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                // Insert into database
                $sql = "INSERT INTO events (event_name, event_date, ending_time, location, description, picture) 
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $event_name, $event_date, $ending_time, $location, $description, $relative_path);

                if ($stmt->execute()) {
                    $message = "Event created successfully!";
                    echo json_encode([
                        'status' => 'success',
                        'message' => $message
                    ]);
                    exit;
                } else {
                    throw new Exception("Error creating event: " . $stmt->error);
                }
                $stmt->close();
            } else {
                throw new Exception("Error uploading file");
            }
        } else {
            throw new Exception("Please select an image file");
        }
    } catch (Exception $e) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            // If AJAX request
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        } else {
            // If regular form submit
            $message = "<div class='error'>" . $e->getMessage() . "</div>";
        }
    }
}
?>

<div>
    <?php if ($message): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <h1>Create New Event</h1>

    <form id="eventForm" method="POST" enctype="multipart/form-data">
        <div class="form-container">
            <div class="forms">
                <div class="form-group">
                    <label>Event Name:</label>
                    <input type="text" name="event_name" required maxlength="50">
                </div>

                <div class="form-group">
                    <label>Event Date:</label>
                    <input type="datetime-local" name="event_date" required>
                </div>

                <div class="form-group">
                    <label>Ending Time:</label>
                    <input type="datetime-local" name="ending_time" required>
                </div>

                <div class="form-group">
                    <label>Location:</label>
                    <input type="text" name="location" required maxlength="20">
                </div>

                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" required maxlength="200"
                        oninput="if(this.value.length > 200) this.value = this.value.slice(0,200);"></textarea>
                    <small>Maximum 200 characters</small>
                </div>

                <div class="form-group">
                    <label>Event Picture:</label>
                    <input type="file" name="picture" accept="image/*" required>
                </div>
            </div>
        </div>
        <button type="submit">Create Event</button>
    </form>
    <script>
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Add this line to show loading state
            document.querySelector('button[type="submit"]').disabled = true;

            fetch('create_events.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Remove any existing message divs
                const existingMessages = document.querySelectorAll('.message-div');
                existingMessages.forEach(div => div.remove());

                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.status === 'success') {
                        // Redirect on success
                        //window.location.replace('view_events.php');
                        window.location.href = 'employeePortal.php?tab=view_events';
                    } else {
                        // Show error message
                        showMessage(jsonData.message, 'error');
                    }
                } catch (e) {
                    // If response isn't JSON, check if it contains success message
                    if (data.includes('success')) {
                       // window.location.replace('view_events.php');
                       window.location.href = 'employeePortal.php?tab=view_events';
                    } else {
                        // Show raw error message
                        showMessage('An error occurred while creating the event', 'error');
                        console.error('Response:', data);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while submitting the form', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                document.querySelector('button[type="submit"]').disabled = false;
            });
        });

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-div ${type}`;
            messageDiv.textContent = message;
            const form = document.getElementById('eventForm');
            form.parentNode.insertBefore(messageDiv, form);
        }

        // Description length validation
        document.querySelector('textarea[name="description"]').addEventListener('input', function() {
            if (this.value.length > 200) {
                this.value = this.value.substring(0, 200);
            }
        });
    </script>
</div>
