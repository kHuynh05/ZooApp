<?php
include '../scripts/employeeRole.php';

?>
<link rel="stylesheet" href="../assets/css/feeding.css">
<div class="content-section">
    <h2>Animal Feeding Management</h2>

    <!-- Show Assigned Enclosures -->
    <div class="assigned-enclosures">
        <h3>Your Assigned Enclosures</h3>
        <?php
        // Get caretaker's assigned enclosures
        $emp_id = $_SESSION['emp_id'];
        $enclosure_sql = "SELECT e.enclosure_id, e.enclosure_name 
                FROM enclosures e 
                JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                WHERE c.emp_id = ?";
        $enclosure_stmt = $conn->prepare($enclosure_sql);
        $enclosure_stmt->bind_param("i", $emp_id);
        $enclosure_stmt->execute();
        $enclosure_result = $enclosure_stmt->get_result();

        if ($enclosure_result->num_rows > 0) {
            echo "<ul class='enclosure-list'>";
            while ($row = $enclosure_result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['enclosure_name']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='warning'>You have no assigned enclosures. Please contact your supervisor.</p>";
        }
        ?>
    </div>

    <!-- Animal Conditions Section -->
    <div class="animal-conditions-section">
        <h3>Animal Health & Mood Status</h3>
        <?php
        // Get animals and their conditions from assigned enclosures
        $mood_sql = "SELECT a.animal_id, a.animal_name, s.species_name, ac.mood, ac.health_status, ac.recorded_at
                FROM animals a
                JOIN species s ON a.species_id = s.species_id
                JOIN enclosures e ON s.enclosure_id = e.enclosure_id
                JOIN caretaker c ON e.enclosure_id = c.enclosure_id
                LEFT JOIN (
                    SELECT ac1.*
                    FROM animal_conditions ac1
                    INNER JOIN (
                        SELECT animal_id, MAX(recorded_at) as max_date
                        FROM animal_conditions
                        GROUP BY animal_id
                    ) ac2 ON ac1.animal_id = ac2.animal_id AND ac1.recorded_at = ac2.max_date
                ) ac ON a.animal_id = ac.animal_id
                WHERE c.emp_id = ?
                ORDER BY e.enclosure_name, a.animal_name";

        $mood_stmt = $conn->prepare($mood_sql);
        $mood_stmt->bind_param("i", $emp_id);
        $mood_stmt->execute();
        $mood_result = $mood_stmt->get_result();

        if ($mood_result->num_rows > 0) {
            echo "<div class='animal-conditions-table'>";
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>Animal</th>";
            echo "<th>Species</th>";
            echo "<th>Mood</th>";
            echo "<th>Health Status</th>";
            echo "<th>Last Updated</th>";
            echo "</tr></thead><tbody>";

            while ($row = $mood_result->fetch_assoc()) {
                $moodClass = 'mood-' . strtolower($row['mood'] ?? 'unknown');
                $healthClass = 'health-' . strtolower($row['health_status'] ?? 'unknown');

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['animal_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['species_name']) . "</td>";
                echo "<td class='{$moodClass}'>" . htmlspecialchars($row['mood'] ?? 'Not recorded') . "</td>";
                echo "<td class='{$healthClass}'>" . htmlspecialchars($row['health_status'] ?? 'Not recorded') . "</td>";
                echo "<td>" . htmlspecialchars($row['recorded_at'] ?? 'Never') . "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo "<p class='warning'>No animals found in your assigned enclosures.</p>";
        }
        ?>
    </div>

    <!-- Feeding Form -->
    <div class="feeding-form">
        <h3>Record New Feeding</h3>
        <?php
        // Check if caretaker has any assignments
        $has_assignments = $enclosure_result->num_rows > 0;
        if ($has_assignments):
        ?>
            <form action="process_feeding.php" method="POST">
                <div class="form-group">
                    <label for="enclosure">Select Enclosure:</label>
                    <select name="enclosure_id" id="enclosure" required onchange="loadAnimals(this.value)">
                        <option value="">Select an enclosure</option>
                        <?php
                        // Reset result pointer
                        $enclosure_result->data_seek(0);
                        while ($row = $enclosure_result->fetch_assoc()) {
                            echo "<option value='" . $row['enclosure_id'] . "'>" .
                                htmlspecialchars($row['enclosure_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="animal">Select Animal:</label>
                    <select name="animal_id" id="animal" required>
                        <option value="">First select an enclosure</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="food_type">Food Type:</label>
                    <input type="text" name="food_type" required>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity (kg):</label>
                    <input type="number" name="quantity" step="0.1" required>
                </div>

                <div class="form-group">
                    <label for="feeding_time">Feeding Time:</label>
                    <input type="datetime-local" name="feeding_time" required>
                </div>

                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>

                <button type="submit" class="btn">Record Feeding</button>
            </form>
        <?php else: ?>
            <p class="warning">You need to be assigned to enclosures before you can record feedings.</p>
        <?php endif; ?>
    </div>

    <!-- Recent Feeding Records -->
    <div class="feeding-history">
        <h3>Recent Feeding Records for Your Enclosures</h3>
        <table>
            <thead>
                <tr>
                    <th>Enclosure</th>
                    <th>Animal</th>
                    <th>Food Type</th>
                    <th>Quantity</th>
                    <th>Time</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $feeding_sql = "SELECT f.*, a.animal_name, s.species_name, e.enclosure_name, f.kg_quantity 
                        FROM feeding_records f 
                        JOIN animals a ON f.animal_id = a.animal_id 
                        JOIN species s ON a.species_id = s.species_id
                        JOIN enclosures e ON s.enclosure_id = e.enclosure_id 
                        JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                        WHERE c.emp_id = ? AND a.deleted = 0 AND s.deleted = 0
                        ORDER BY f.feeding_time DESC LIMIT 10";
                $feeding_stmt = $conn->prepare($feeding_sql);
                $feeding_stmt->bind_param("i", $emp_id);
                $feeding_stmt->execute();
                $feeding_result = $feeding_stmt->get_result();

                while ($row = $feeding_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['enclosure_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['animal_name']) . " (" . htmlspecialchars($row['species_name']) . ")</td>";
                    echo "<td>" . htmlspecialchars($row['food_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['kg_quantity']) . " kg</td>";
                    echo "<td>" . htmlspecialchars($row['feeding_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add JavaScript for dynamic animal loading -->
<script>
    function loadAnimals(enclosureId) {
        if (!enclosureId) {
            document.getElementById('animal').innerHTML = '<option value="">First select an enclosure</option>';
            return;
        }

        // Show loading state
        document.getElementById('animal').innerHTML = '<option value="">Loading animals...</option>';

        // Fetch animals for selected enclosure using AJAX
        fetch(`../scripts/get_enclosure_animals.php?enclosure_id=${enclosureId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(animals => {
                if (animals.error) {
                    throw new Error(animals.error);
                }

                let options = '<option value="">Select an animal</option>';
                if (animals.length > 0) {
                    animals.forEach(animal => {
                        options += `<option value="${animal.animal_id}">${animal.animal_name} (${animal.species_name})</option>`;
                    });
                } else {
                    options = '<option value="">No animals found in this enclosure</option>';
                }
                document.getElementById('animal').innerHTML = options;
            })
            .catch(error => {
                console.error('Error loading animals:', error);
                document.getElementById('animal').innerHTML = '<option value="">Error loading animals</option>';
            });
    }
</script>