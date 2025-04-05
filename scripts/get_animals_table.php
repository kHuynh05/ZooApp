<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

$sql = "SELECT a.*, s.species_name 
        FROM animals a 
        JOIN species s ON a.species_id = s.species_id
        WHERE a.deleted = FALSE AND s.deleted = FALSE
        ORDER BY a.animal_name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>Animal Name</th>";
    echo "<th>Species</th>";
    echo "<th>Date of Birth</th>";
    echo "<th>Sex</th>";
    echo "<th>Status</th>";
    echo "<th>Action</th>";
    echo "</tr></thead><tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['animal_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['species_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sex']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='action' value='remove_animal'>
                    <input type='hidden' name='animal_id' value='" . $row['animal_id'] . "'>
                    <button type='submit' class='btn-cancel'>Remove</button>
                </form>
              </td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No animals found.</p>";
}
