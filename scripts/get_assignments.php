<?php
include '../scripts/employeeRole.php';
include '../config/database.php';

// Update the SQL query to order by enclosure_name first, then emp_name
$sql = "SELECT c.emp_id, c.enclosure_id, e.emp_name, enc.enclosure_name
        FROM caretaker c
        JOIN employees e ON c.emp_id = e.emp_id
        JOIN enclosures enc ON c.enclosure_id = enc.enclosure_id
        ORDER BY enc.enclosure_name, e.emp_name";
$result = $conn->query($sql);
?>

<table>
    <thead>
        <tr>
            <th>Enclosure</th>
            <th>Caretaker</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            $current_enclosure = null;
            while ($row = $result->fetch_assoc()) {
                // If this is a new enclosure, add the enclosure header
                if ($current_enclosure !== $row['enclosure_name']) {
                    // Add the enclosure header
                    echo "<tr class='enclosure-header'>";
                    echo "<td colspan='3'>" . htmlspecialchars($row['enclosure_name']) . "</td>";
                    echo "</tr>";
                    $current_enclosure = $row['enclosure_name'];
                }

                // Add the caretaker row
                echo "<tr class='caretaker-row'>";
                echo "<td></td>"; // Empty cell for indentation
                echo "<td>" . htmlspecialchars($row['emp_name']) . "</td>";
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
        } else {
            echo "<tr><td colspan='3'>No caretaker assignments found.</td></tr>";
        }
        ?>
    </tbody>
</table>