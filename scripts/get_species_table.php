<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    exit();
}

$sql = "SELECT s.*, e.enclosure_name 
        FROM species s 
        JOIN enclosures e ON s.enclosure_id = e.enclosure_id";
$result = $conn->query($sql);

echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>Species Name</th>";
echo "<th>Class</th>";
echo "<th>Habitat</th>";
echo "<th>Enclosure</th>";
echo "<th>Action</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

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

echo "</tbody>";
echo "</table>";
