<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

$sql = "SELECT s.*, e.enclosure_name 
        FROM species s 
        JOIN enclosures e ON s.enclosure_id = e.enclosure_id
        WHERE s.deleted = 0
        ORDER BY s.species_name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>Species Name</th>";
    echo "<th>Class</th>";
    echo "<th>Habitat</th>";
    echo "<th>Enclosure</th>";
    echo "<th>Action</th>";
    echo "</tr></thead><tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['species_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['class']) . "</td>";
        echo "<td>" . htmlspecialchars($row['habitat']) . "</td>";
        echo "<td>" . htmlspecialchars($row['enclosure_name']) . "</td>";
        echo "<td>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='action' value='remove_species'>
                    <input type='hidden' name='species_id' value='" . $row['species_id'] . "'>
                    <button type='submit' class='btn-cancel'>Remove</button>
                </form>
              </td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No species found.</p>";
}
