<?php
include '../scripts/employeeRole.php';
include '../config/database.php';  // Add database connection

// Check if user has permission to assign care
if (!in_array('assign_care', $allowed_actions)) {
    header("Location: employeePortal.php");
    exit();
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        // Handle removal
        $emp_id = $_POST['emp_id'];
        $enclosure_id = $_POST['enclosure_id'];

        // Delete the assignment
        $remove_sql = "DELETE FROM caretaker WHERE emp_id = ? AND enclosure_id = ?";
        $remove_stmt = $conn->prepare($remove_sql);
        $remove_stmt->bind_param("ii", $emp_id, $enclosure_id);

        if ($remove_stmt->execute()) {
            $message = "Caretaker assignment removed successfully!";
            $messageClass = "success";
        } else {
            $message = "Error removing caretaker assignment: " . $remove_stmt->error;
            $messageClass = "error";
        }
        $remove_stmt->close();
    } else {
        // Handle assignment
        $emp_id = $_POST['employee_id'];
        $enclosure_id = $_POST['enclosure_id'];

        // Verify employee has 'care' role
        $sql = "SELECT role FROM employees WHERE emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if ($employee && $employee['role'] == 'care') {
            // Check if assignment already exists
            $check_sql = "SELECT * FROM caretaker WHERE emp_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $emp_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing assignment
                $update_sql = "UPDATE caretaker SET enclosure_id = ? WHERE emp_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $enclosure_id, $emp_id);

                if ($update_stmt->execute()) {
                    $message = "Caretaker assignment updated successfully!";
                    $messageClass = "success";
                } else {
                    $message = "Error updating caretaker assignment: " . $update_stmt->error;
                    $messageClass = "error";
                }
                $update_stmt->close();
            } else {
                // Insert new assignment
                $sql = "INSERT INTO caretaker (emp_id, enclosure_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $emp_id, $enclosure_id);

                if ($stmt->execute()) {
                    $message = "Caretaker assigned successfully!";
                    $messageClass = "success";
                } else {
                    $message = "Error assigning caretaker: " . $stmt->error;
                    $messageClass = "error";
                }
            }
            $check_stmt->close();
        } else {
            $message = "Selected employee is not a caretaker.";
            $messageClass = "error";
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/assign_care.css">
<div class="content-section">
    <h2>Assign Caretakers to Enclosures</h2>

    <div id="message-container" class="message-container"></div>

    <div class="assignment-form">
        <form id="assignForm" method="POST">
            <div class="form-group">
                <label for="employee_id">Select Caretaker:</label>
                <select name="employee_id" id="employee_id" required>
                    <option value="">Select a caretaker</option>
                    <?php
                    // Get all employees with 'care' role
                    $sql = "SELECT emp_id, emp_name FROM employees WHERE role = 'care'";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['emp_id'] . "'>" .
                            htmlspecialchars($row['emp_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="enclosure_id">Select Enclosure:</label>
                <select name="enclosure_id" id="enclosure_id" required>
                    <option value="">Select an enclosure</option>
                    <?php
                    // Get all enclosures
                    $sql = "SELECT enclosure_id, enclosure_name FROM enclosures";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['enclosure_id'] . "'>" .
                            htmlspecialchars($row['enclosure_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn-submit">Assign Caretaker</button>
        </form>
    </div>

    <!-- Current Assignments Table -->
    <div class="assignments-table">
        <h3>Current Caretaker Assignments</h3>
        <div id="assignmentsTableContainer">
            <?php include '../scripts/get_assignments.php'; ?>
        </div>
    </div>
</div>

<script>
    // Function to show message
    function showMessage(message, isSuccess) {
        const messageContainer = document.getElementById('message-container');
        messageContainer.innerHTML = `
            <div class="message ${isSuccess ? 'success' : 'error'}">
                ${message}
            </div>
        `;
        // Remove message after 5 seconds
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }

    // Function to refresh assignments table
    function refreshAssignments() {
        fetch('../scripts/get_assignments.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('assignmentsTableContainer').innerHTML = html;
            });
    }

    // Handle assign form submission
    document.getElementById('assignForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../scripts/handle_assign.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success);
                if (data.success) {
                    this.reset();
                    refreshAssignments();
                }
            })
            .catch(error => {
                showMessage('An error occurred while processing your request.', false);
            });
    });

    // Handle remove buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn-cancel')) {
            e.preventDefault();
            const form = e.target.closest('form');
            const formData = new FormData(form);

            fetch('../scripts/handle_assign.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success);
                    if (data.success) {
                        refreshAssignments();
                    }
                })
                .catch(error => {
                    showMessage('An error occurred while processing your request.', false);
                });
        }
    });
</script>