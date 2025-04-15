<?php
include '../scripts/employeeRole.php';
include '../config/database.php';

// Get current caretaker assignments
$sql = "SELECT c.emp_id, c.enclosure_id, e.emp_name, enc.enclosure_name
        FROM caretaker c
        JOIN employees e ON c.emp_id = e.emp_id
        JOIN enclosures enc ON c.enclosure_id = enc.enclosure_id
        ORDER BY e.emp_name";
$result = $conn->query($sql);
?>

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
        if ($result->num_rows > 0) {
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
        } else {
            echo "<tr><td colspan='3'>No caretaker assignments found.</td></tr>";
        }
        ?>
    </tbody>
</table>