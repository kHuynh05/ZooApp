<?php
include '../scripts/employeeRole.php';

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all animals for the dropdown
$animals_query = "SELECT a.animal_id, a.animal_name, s.species_name 
                  FROM animals a
                  JOIN species s ON a.species_id = s.species_id 
                  WHERE a.deleted = 0 AND s.deleted = 0
                  ORDER BY a.animal_name";
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

// Fetch summarized health status data
$summarySql = "SELECT status, COUNT(*) as count 
               FROM animals 
               GROUP BY status";
$summaryResult = $conn->query($summarySql);

$summaryData = [];
if ($summaryResult->num_rows > 0) {
    while ($row = $summaryResult->fetch_assoc()) {
        $summaryData[$row['status']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Animal Medical Records</title>
    <style>

        .filter-container {
            background-color: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-row select,
        .filter-row input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .filter-row select:focus,
        .filter-row input:focus {
            border-color: #3498db;
            outline: none;
        }

        .records-container {
            max-height: 530px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
        }

        .records-table th {
            position: sticky;
            top: 0;
            background-color: #3498db;
            color: white;
            z-index: 5;
        }

        .records-table th,
        .records-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .records-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .records-table tr:hover {
            background-color: #e1f5fe;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
        }

        .btn-filter {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-filter:hover {
            background-color: #2980b9;
        }

        .summary-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .summary-container h2 {
            margin-top: 0;
            color: #333;
        }

        .summary-container ul {
            list-style-type: none;
            padding: 0;
        }

        .summary-container li {
            padding: 5px 0;
            font-size: 16px;
            color: #555;
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
                    <th>Weight (kg)</th>
                    <th>Mood</th>
                    <th>Health Status</th>
                    <th>Veterinarian</th>
                    <th>Additional Notes</th>
                </tr>
            </thead>
            <tbody id="records-body">
                <!-- Records will be loaded dynamically -->
            </tbody>
        </table>
    </div>

    <div class="summary-container">
        <h2>Animal Health Status Summary</h2>
        <ul>
            <li>Well: <?php echo $summaryData['well'] ?? 0; ?></li>
            <li>Sick: <?php echo $summaryData['sick'] ?? 0; ?></li>
            <li>Recovering: <?php echo $summaryData['recovering'] ?? 0; ?></li>
        </ul>
    </div>

    <script>
        function applyFilters() {
            const animalId = document.getElementById('animal-filter').value;
            const vetId = document.getElementById('vet-filter').value;
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            // Validate dates
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('Start date must be before or equal to end date');
                return;
            }

            // Show loading state
            const recordsBody = document.getElementById('records-body');
            recordsBody.innerHTML = '<tr><td colspan="9" style="text-align: center;">Loading...</td></tr>';

            // Create form data for the POST request
            const formData = new FormData();
            if (animalId) formData.append('animal_id', animalId);
            if (vetId) formData.append('vet_id', vetId);
            if (startDate) formData.append('startDate', startDate);
            if (endDate) formData.append('endDate', endDate);

            // Fetch data from PHP script
            fetch('../scripts/get_medical_records.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    renderRecords(data.records);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    recordsBody.innerHTML = '<tr><td colspan="9" style="text-align: center; color: red;">Error loading data. Please try again.</td></tr>';
                });
        }

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
        window.onload = () => applyFilters();
    </script>
</body>

</html>