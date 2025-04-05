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
        $sql = "SELECT e.enclosure_id, e.enclosure_name 
                FROM enclosures e 
                JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                WHERE c.emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<ul class='enclosure-list'>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['enclosure_name']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='warning'>You have no assigned enclosures. Please contact your supervisor.</p>";
        }
        ?>
    </div>

    <!-- Feeding Form -->
    <div class="feeding-form">
        <h3>Record New Feeding</h3>
        <?php
        // Check if caretaker has any assignments
        $has_assignments = $result->num_rows > 0;
        if ($has_assignments):
        ?>
            <form action="process_feeding.php" method="POST">
                <div class="form-group">
                    <label for="enclosure">Select Enclosure:</label>
                    <select name="enclosure_id" id="enclosure" required onchange="loadAnimals(this.value)">
                        <option value="">Select an enclosure</option>
                        <?php
                        // Reset result pointer
                        $result->data_seek(0);
                        while ($row = $result->fetch_assoc()) {
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
                $sql = "SELECT f.*, a.animal_name, s.species_name, e.enclosure_name, f.kg_quantity 
                        FROM feeding_records f 
                        JOIN animals a ON f.animal_id = a.animal_id 
                        JOIN species s ON a.species_id = s.species_id
                        JOIN enclosures e ON s.enclosure_id = e.enclosure_id 
                        JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                        WHERE c.emp_id = ?
                        ORDER BY f.feeding_time DESC LIMIT 10";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $emp_id);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
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

        // Fetch animals for selected enclosure using AJAX
        fetch(`get_enclosure_animals.php?enclosure_id=${enclosureId}`)
            .then(response => response.json())
            .then(animals => {
                let options = '<option value="">Select an animal</option>';
                animals.forEach(animal => {
                    options += `<option value="${animal.animal_id}">${animal.animal_name} (${animal.species_name})</option>`;
                });
                document.getElementById('animal').innerHTML = options;
            })
            .catch(error => console.error('Error loading animals:', error));
    }
</script>