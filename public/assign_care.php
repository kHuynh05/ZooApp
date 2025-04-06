<?php
include '../scripts/employeeRole.php';

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

        $sql = "DELETE FROM caretaker WHERE emp_id = ? AND enclosure_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $emp_id, $enclosure_id);

        if ($stmt->execute()) {
            $message = "Caretaker assignment removed successfully!";
            $messageClass = "success";
        } else {
            $message = "Error removing caretaker assignment: " . $stmt->error;
            $messageClass = "error";
        }
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

    <?php if (isset($message)): ?>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="assignment-form">
        <form method="POST" action="">
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
        <table>
            <thead>
                <tr>
                    <th>Caretaker</th>
                    <th>Enclosure</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get current caretaker assignments
                $sql = "SELECT c.emp_id, c.enclosure_id, e.emp_name, enc.enclosure_name
                        FROM caretaker c
                        JOIN employees e ON c.emp_id = e.emp_id
                        JOIN enclosures enc ON c.enclosure_id = enc.enclosure_id
                        ORDER BY e.emp_name";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['emp_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['enclosure_name']) . "</td>";
                    echo "<td>
                            <form method='POST' action='' style='display:inline;'>
                                <input type='hidden' name='action' value='remove'>
                                <input type='hidden' name='emp_id' value='" . $row['emp_id'] . "'>
                                <input type='hidden' name='enclosure_id' value='" . $row['enclosure_id'] . "'>
                                <button type='submit' class='btn-cancel'>Remove</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>