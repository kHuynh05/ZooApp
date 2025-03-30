<?php
include '../scripts/employeeRole.php';

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all animals for the dropdown
$animals_query = "SELECT animal_id, animal_name, species_name 
                  FROM animals 
                  JOIN species ON animals.species_id = species.species_id 
                  ORDER BY animal_name";
$animals_result = $conn->query($animals_query);
$animals = [];
if ($animals_result->num_rows > 0) {
    while ($row = $animals_result->fetch_assoc()) {
        $animals[] = $row;
    }
}

// Fetch all employees (vets) for the dropdown
$employees_query = "SELECT emp_id, emp_name
                    FROM employees
                    WHERE role = 'vet'";
$employees_result = $conn->query($employees_query);
$employees = [];
if ($employees_result->num_rows > 0) {
    while ($row = $employees_result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Fetch all medical records
$query = "SELECT ac.*, 
                 a.animal_name, 
                 s.species_name, 
                 e.emp_name,
                 enc.enclosure_name
          FROM animal_conditions ac
          JOIN animals a ON ac.animal_id = a.animal_id
          JOIN species s ON a.species_id = s.species_id
          JOIN employees e ON ac.emp_id = e.emp_id
          JOIN enclosures enc ON a.enclosure_id = enc.enclosure_id
          ORDER BY ac.recorded_at DESC
          LIMIT 50";

$result = $conn->query($query);
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Animal Medical Records</title>
    <style>
        .filter-container {
            background-color: #f4f4f4;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-row select,
        .filter-row input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .records-container {
            max-height: 530px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
        }

        .records-table th {
            position: sticky;
            top: 0;
            background-color: #f2f2f2;
            z-index: 5;
        }

        .records-table th,
        .records-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .records-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .records-table tr:hover {
            background-color: #f5f5f5;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <h1>Animal Medical Records</h1>

    <div class="filter-container">
        <div class="filter-row">
            <select id="animal-filter">
                <option value="">All Animals</option>
                <?php foreach ($animals as $animal): ?>
                    <option value="<?php echo $animal['animal_id']; ?>">
                        <?php echo htmlspecialchars($animal['animal_name'] . ' (' . $animal['species_name'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="vet-filter">
                <option value="">All Veterinarians</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo $employee['emp_id']; ?>">
                        <?php echo htmlspecialchars($employee['emp_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-row">
            <input type="date" id="start-date" placeholder="Start Date">
            <input type="date" id="end-date" placeholder="End Date">
            <button onclick="applyFilters()" class="btn-filter">Apply Filters</button>
        </div>
    </div>

    <div class="records-container">
        <table class="records-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Animal</th>
                    <th>Species</th>
                    <th>Habitat</th>
                    <th>Weight (kg)</th>
                    <th>Mood</th>
                    <th>Health Status</th>
                    <th>Veterinarian</th>
                    <th>Additional Notes</th>
                </tr>
            </thead>
            <tbody id="records-body">
                <!-- Rows will be dynamically populated -->
            </tbody>
        </table>
    </div>

    <script>
        // Store all records from PHP
        const allRecords = <?php echo json_encode($records); ?>;

        // Initial load of records
        function initialLoad() {
            renderRecords(allRecords);
        }

        // Apply filters
        function applyFilters() {
            const animalFilter = document.getElementById('animal-filter').value;
            const vetFilter = document.getElementById('vet-filter').value;
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            // Filter records
            const filteredRecords = allRecords.filter(record => {
                // Animal filter
                const animalCheck = !animalFilter || record.animal_id == animalFilter;

                // Veterinarian filter
                const vetCheck = !vetFilter || record.emp_id == vetFilter;

                // Date filter
                const recordDate = new Date(record.recorded_at);
                const start = startDate ? new Date(startDate) : null;
                const end = endDate ? new Date(endDate) : null;
                const dateCheck = (!start || recordDate >= start) &&
                    (!end || recordDate <= end);

                return animalCheck && vetCheck && dateCheck;
            });

            renderRecords(filteredRecords);
        }

        // Render records in the table
        function renderRecords(records) {
            const recordsBody = document.getElementById('records-body');
            recordsBody.innerHTML = ''; // Clear existing rows

            if (records.length === 0) {
                recordsBody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; background-color: #f4f4f4;">
                            No medical records found matching the selected filters.
                        </td>
                    </tr>
                `;
                return;
            }

            records.forEach(record => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${record.recorded_at}</td>
                    <td>${record.animal_name}</td>
                    <td>${record.species_name}</td>
                    <td>${record.enclosure_name}</td>
                    <td>${record.weight}</td>
                    <td>${record.mood}</td>
                    <td>${record.health_status}</td>
                    <td>${record.emp_name}</td>
                    <td>${record.additional_notes}</td>
                `;
                recordsBody.appendChild(row);
            });
        }

        // Load initial records when page loads
        window.onload = initialLoad;
    </script>
</body>
</html>