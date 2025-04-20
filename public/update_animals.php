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

                // Handle image upload
                $img_path = null;
                if (isset($_FILES['species_image']) && $_FILES['species_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['species_image'];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    // Validate file type - only allow jpg
                    if ($file_extension !== 'jpg') {
                        $message = "Error: Only JPG files are allowed.";
                        $messageClass = "error";
                        break;
                    }

                    // Create standardized filename
                    $standardized_name = strtolower(str_replace(' ', '-', trim($species_name))) . '-animal.jpg';
                    $upload_path = '../assets/img/' . $standardized_name;

                    // Check if file already exists
                    if (file_exists($upload_path)) {
                        $message = "Error: An image for this species already exists.";
                        $messageClass = "error";
                        break;
                    }

                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $img_path = $standardized_name;
                    } else {
                        $message = "Error uploading file.";
                        $messageClass = "error";
                        break;
                    }
                }

                $sql = "INSERT INTO species (species_name, class, habitat, diet_type, conservation_status, description, fun_fact, enclosure_id, img) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssss", $species_name, $class, $habitat, $diet_type, $conservation_status, $description, $fun_fact, $enclosure_id, $img_path);

                if ($stmt->execute()) {
                    $message = "Species added successfully!";
                    $messageClass = "success";
                } else {
                    // If database insert fails, remove the uploaded image
                    if ($img_path && file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                    $message = "Error adding species: " . $stmt->error;
                    $messageClass = "error";
                }
                break;

            case 'remove_species':
                $species_id = $_POST['species_id'];

                // Check if there are any animals of this species
                $check_sql = "SELECT COUNT(*) as count FROM animals WHERE species_id = ? AND deleted = 0";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $species_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    $message = "Cannot remove species: There are still animals of this species in the zoo.";
                    $messageClass = "error";
                } else {
                    // Only proceed with deletion if no animals exist
                    $sql = "UPDATE species SET deleted = 1 WHERE species_id = ?";
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
                $check_stmt->close();
                break;

            case 'add_animal':
                $animal_name = $_POST['animal_name'];
                $date_of_birth = $_POST['date_of_birth'];
                $date_of_rescue = $_POST['date_of_rescue'];
                $sex = $_POST['sex'];
                $status = $_POST['status'];
                $species_id = $_POST['species_id'];

                $sql = "INSERT INTO animals (animal_name, date_of_birth, date_of_rescue, sex, status, species_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $animal_name, $date_of_birth, $date_of_rescue, $sex, $status, $species_id);

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
                $sql = "UPDATE animals SET deleted = 1 WHERE animal_id = ?";
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

    <div id="message-container"></div>

    <!-- Species Management -->
    <div class="management-form">
        <h3>Species Management</h3>
        <form id="speciesForm" method="POST" enctype="multipart/form-data">
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
                <select name="conservation_status" required>
                    <option value="stable">Stable</option>
                    <option value="vulnerable">Vulnerable</option>
                    <option value="endangered">Endangered</option>
                </select>
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
            <div class="form-group">
                <label for="species_image">Species Image (JPG only):</label>
                <input type="file" name="species_image" accept=".jpg" required class="file-input">
                <p class="file-help">Image will be saved as: [species-name]-animal.jpg</p>
            </div>
            <button type="submit" class="btn-submit">Add Species</button>
        </form>
    </div>

    <!-- Animal Management -->
    <div class="management-form">
        <h3>Animal Management</h3>
        <form id="animalForm" method="POST">
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
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" required>
                    <option value="sick">Sick</option>
                    <option value="well">Well</option>
                    <option value="recovering">Recovering</option>
                </select>
            </div>
            <div class="form-group">
                <label for="species_id">Species:</label>
                <select name="species_id" required>
                    <option value="">Select a species</option>
                    <?php
                    $sql = "SELECT species_id, species_name FROM species WHERE deleted = 0 ORDER BY species_name";
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
        <div id="speciesTableContainer">
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
                            JOIN enclosures e ON s.enclosure_id = e.enclosure_id AND s.deleted = 0
                            ORDER BY s.species_name";
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
    </div>

    <!-- Current Animals List -->
    <div class="management-table">
        <h3>Current Animals</h3>
        <div id="animalsTableContainer">
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
                            JOIN species s ON a.species_id = s.species_id AND a.deleted = 0
                            ORDER BY a.animal_name";
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
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }

    // Function to refresh tables
    function refreshTables() {
        // Refresh species table
        fetch('../scripts/get_species_table.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('speciesTableContainer').innerHTML = html;
            });

        // Refresh animals table
        fetch('../scripts/get_animals_table.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('animalsTableContainer').innerHTML = html;
            });
    }

    // Handle species form submission
    document.getElementById('speciesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../scripts/handle_species.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success);
                if (data.success) {
                    this.reset();
                    refreshTables();
                }
            })
            .catch(error => {
                showMessage('An error occurred while processing your request.', false);
            });
    });

    // Handle animal form submission
    document.getElementById('animalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../scripts/handle_animal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success);
                if (data.success) {
                    this.reset();
                    refreshTables();
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

            fetch('../scripts/handle_remove.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success);
                    if (data.success) {
                        refreshTables();
                    }
                })
                .catch(error => {
                    showMessage('An error occurred while processing your request.', false);
                });
        }
    });
</script>