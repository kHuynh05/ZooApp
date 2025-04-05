<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    header("Location: employeePortal.php");
    exit();
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_species':
                $species_name = $_POST['species_name'];
                $class = $_POST['class'];
                $habitat = $_POST['habitat'];
                $diet_type = $_POST['diet_type'];
                $conservation_status = $_POST['conservation_status'];
                $description = $_POST['description'];
                $fun_fact = $_POST['fun_fact'];
                $enclosure_id = $_POST['enclosure_id'];

                $sql = "INSERT INTO species (species_name, class, habitat, diet_type, conservation_status, description, fun_fact, enclosure_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $species_name, $class, $habitat, $diet_type, $conservation_status, $description, $fun_fact, $enclosure_id);

                if ($stmt->execute()) {
                    $message = "Species added successfully!";
                    $messageClass = "success";
                } else {
                    $message = "Error adding species: " . $stmt->error;
                    $messageClass = "error";
                }
                break;

            case 'remove_species':
                $species_id = $_POST['species_id'];

                // Check if there are any animals of this species
                $check_sql = "SELECT COUNT(*) as count FROM animals WHERE species_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $species_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    $message = "Cannot remove species: There are still animals of this species in the zoo.";
                    $messageClass = "error";
                } else {
                    $sql = "DELETE FROM species WHERE species_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $species_id);

                    if ($stmt->execute()) {
                        $message = "Species removed successfully!";
                        $messageClass = "success";
                    } else {
                        $message = "Error removing species: " . $stmt->error;
                        $messageClass = "error";
                    }
                }
                break;

            case 'add_animal':
                $animal_name = $_POST['animal_name'];
                $date_of_birth = $_POST['date_of_birth'];
                $date_of_rescue = $_POST['date_of_rescue'];
                $sex = $_POST['sex'];
                $fact = $_POST['fact'];
                $status = $_POST['status'];
                $species_id = $_POST['species_id'];

                $sql = "INSERT INTO animals (animal_name, date_of_birth, date_of_rescue, sex, fact, status, species_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $animal_name, $date_of_birth, $date_of_rescue, $sex, $fact, $status, $species_id);

                if ($stmt->execute()) {
                    $message = "Animal added successfully!";
                    $messageClass = "success";
                } else {
                    $message = "Error adding animal: " . $stmt->error;
                    $messageClass = "error";
                }
                break;

            case 'remove_animal':
                $animal_id = $_POST['animal_id'];

                // First delete any conditions for this animal
                $sql = "DELETE FROM animal_conditions WHERE animal_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $animal_id);
                $stmt->execute();

                // Then delete the animal
                $sql = "DELETE FROM animals WHERE animal_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $animal_id);

                if ($stmt->execute()) {
                    $message = "Animal removed successfully!";
                    $messageClass = "success";
                } else {
                    $message = "Error removing animal: " . $stmt->error;
                    $messageClass = "error";
                }
                break;
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/update_animals.css">
<div class="content-section">
    <h2>Animal Management</h2>

    <?php if (isset($message)): ?>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Species Management -->
    <div class="management-form">
        <h3>Species Management</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_species">
            <div class="form-group">
                <label for="species_name">Species Name:</label>
                <input type="text" name="species_name" required>
            </div>
            <div class="form-group">
                <label for="class">Class:</label>
                <input type="text" name="class" required>
            </div>
            <div class="form-group">
                <label for="habitat">Habitat:</label>
                <input type="text" name="habitat" required>
            </div>
            <div class="form-group">
                <label for="diet_type">Diet Type:</label>
                <input type="text" name="diet_type" required>
            </div>
            <div class="form-group">
                <label for="conservation_status">Conservation Status:</label>
                <input type="text" name="conservation_status" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="fun_fact">Fun Fact:</label>
                <textarea name="fun_fact" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label for="enclosure_id">Enclosure:</label>
                <select name="enclosure_id" required>
                    <option value="">Select an enclosure</option>
                    <?php
                    $sql = "SELECT enclosure_id, enclosure_name FROM enclosures";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['enclosure_id'] . "'>" .
                            htmlspecialchars($row['enclosure_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Add Species</button>
        </form>
    </div>

    <!-- Animal Management -->
    <div class="management-form">
        <h3>Animal Management</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_animal">
            <div class="form-group">
                <label for="animal_name">Animal Name:</label>
                <input type="text" name="animal_name" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="date_of_rescue">Date of Rescue:</label>
                <input type="date" name="date_of_rescue" required>
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select name="sex" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="fact">Fact:</label>
                <textarea name="fact" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="species_id">Species:</label>
                <select name="species_id" required>
                    <option value="">Select a species</option>
                    <?php
                    $sql = "SELECT species_id, species_name FROM species";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['species_id'] . "'>" .
                            htmlspecialchars($row['species_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Add Animal</button>
        </form>
    </div>

    <!-- Current Species List -->
    <div class="management-table">
        <h3>Current Species</h3>
        <table>
            <thead>
                <tr>
                    <th>Species Name</th>
                    <th>Class</th>
                    <th>Habitat</th>
                    <th>Enclosure</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT s.*, e.enclosure_name 
                        FROM species s 
                        JOIN enclosures e ON s.enclosure_id = e.enclosure_id";
                $result = $conn->query($sql);
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
                ?>
            </tbody>
        </table>
    </div>

    <!-- Current Animals List -->
    <div class="management-table">
        <h3>Current Animals</h3>
        <table>
            <thead>
                <tr>
                    <th>Animal Name</th>
                    <th>Species</th>
                    <th>Date of Birth</th>
                    <th>Sex</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT a.*, s.species_name 
                        FROM animals a 
                        JOIN species s ON a.species_id = s.species_id";
                $result = $conn->query($sql);
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
                ?>
            </tbody>
        </table>
    </div>
</div>