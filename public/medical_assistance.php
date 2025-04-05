<?php
include '../scripts/employeeRole.php';

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit();
}

$formSubmitted = false;
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get emp_id from session
    $emp_id = $_SESSION['emp_id'];

    // Validate inputs
    $animal_id = filter_input(INPUT_POST, 'animal_select', FILTER_VALIDATE_INT);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);

    // Replace FILTER_SANITIZE_STRING with more secure alternatives
    $mood = trim(htmlspecialchars($_POST['mood'] ?? '', ENT_QUOTES, 'UTF-8'));
    $health_status = trim(htmlspecialchars($_POST['health_condition'] ?? '', ENT_QUOTES, 'UTF-8'));
    $additional_notes = trim(htmlspecialchars($_POST['additional_notes'] ?? '', ENT_QUOTES, 'UTF-8'));
    // Validation checks
    $errors = [];
    if (!$animal_id) $errors[] = "Invalid animal selection";
    if ($weight === false || $weight < 0) $errors[] = "Invalid weight";
    if (!in_array($mood, ['calm', 'active', 'agitated', 'stressed', 'playful'])) $errors[] = "Invalid mood";
    if (!in_array($health_status, ['well', 'sick', 'recovering'])) $errors[] = "Invalid health condition";

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO animal_conditions 
            (animal_id, emp_id, weight, mood, health_status, additional_notes) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $animal_id, $emp_id, $weight, $mood, $health_status, $additional_notes);

        if ($stmt->execute()) {
            $formSubmitted = true;
            $message = "Animal condition recorded successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
        $stmt = $conn->prepare("UPDATE animals SET status = ? WHERE animal_id = ?");
        $stmt->bind_param("si", $health_status, $animal_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $message = "Errors: " . implode(", ", $errors);
    }
}

// Fetch animals from database
$sql = "SELECT animal_id, animal_name, species_name, enclosure_name 
        FROM animals
        JOIN enclosures ON species.enclosure_id = enclosures.enclosure_id 
        JOIN species ON animals.species_id = species.species_id";
$result = $conn->query($sql);

$animals = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>Animal Tracking Form</title>
    <style>
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        #animalDetails {
            margin-bottom: 15px;
            border-radius: 4px;
        }

        #submitBtn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #submitBtn:hover {
            background-color: #45a049;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<div class="form-container">
    <h2>Medical Assistance Form</h2>

    <?php if ($formSubmitted): ?>
        <div class="message success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php elseif (!empty($message)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form id="medicalAssistanceForm" method="POST" action="">
        <div class="form-group">
            <label for="animal_select">Select Animal</label>
            <select id="animal_select" name="animal_select" required>
                <option value="">Choose an animal</option>
                <?php foreach ($animals as $animal): ?>
                    <option value="<?php echo $animal['animal_id']; ?>">
                        <?php echo htmlspecialchars($animal['animal_name'] . ' (' . $animal['species_name'] . ') - ' . $animal['enclosure_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="animalDetails" style="display:none;">
            <p><strong>Species:</strong> <span id="speciesDetail"></span></p>
            <p><strong>Habitat:</strong> <span id="habitatDetail"></span></p>
        </div>

        <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" step="0.1" min="0" required>
        </div>

        <div class="form-group">
            <label for="mood">Animal Mood</label>
            <select id="mood" name="mood" required>
                <option value="">Select mood</option>
                <option value="calm">Calm</option>
                <option value="active">Active</option>
                <option value="agitated">Agitated</option>
                <option value="stressed">Stressed</option>
                <option value="playful">Playful</option>
            </select>
        </div>

        <div class="form-group">
            <label for="health_condition">Health Condition</label>
            <select id="health_condition" name="health_condition" required>
                <option value="">Select health status</option>
                <option value="well">Well</option>
                <option value="sick">Sick</option>
                <option value="recovering">Recovering</option>
            </select>
        </div>

        <div class="form-group">
            <label for="additional_notes">Additional Notes</label>
            <textarea id="additional_notes" name="additional_notes" rows="4"></textarea>
        </div>

        <button type="submit" id="submitBtn">Submit Medical Record</button>
    </form>
</div>

<script>
    animalSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedAnimal = JSON.parse(this.value);
            speciesDetail.textContent = selectedAnimal.species_name;
            habitatDetail.textContent = selectedAnimal.enclosure_name; // Changed from habitat to enclosure_name
            animalDetails.style.display = 'block';
        } else {
            animalDetails.style.display = 'none';
        }
    });
    document.getElementById('weight').addEventListener('input', function() {
        if (parseFloat(this.value) < 0) {
            this.value = '';
        }
    });
</script>