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

// Fetch summarized data for the selected animal
$animalSummarySql = "SELECT 
    COUNT(CASE WHEN health_status = 'sick' THEN 1 END) as sick_count,
    COUNT(CASE WHEN health_status = 'well' THEN 1 END) as well_count,
    COUNT(CASE WHEN health_status = 'recovering' THEN 1 END) as recovering_count,
    MAX(CASE WHEN health_status = 'sick' THEN recorded_at END) as last_sick_date,
    (SELECT emp_name FROM employees WHERE emp_id = (SELECT emp_id FROM animal_conditions WHERE animal_id = ? ORDER BY recorded_at DESC LIMIT 1)) as last_vet
    FROM animal_conditions 
    WHERE animal_id = ?";

$stmt = $conn->prepare($animalSummarySql);
$stmt->bind_param("ii", $animalId, $animalId);
$stmt->execute();
$animalSummaryResult = $stmt->get_result();
$animalSummaryData = $animalSummaryResult->fetch_assoc();
$stmt->close();

// Fetch summarized data for the selected veterinarian
$vetSummarySql = "SELECT 
    COUNT(*) as total_reports,
    MAX(recorded_at) as last_report_date,
    (SELECT animal_name FROM animals WHERE animal_id = (SELECT animal_id FROM animal_conditions WHERE emp_id = ? ORDER BY recorded_at DESC LIMIT 1)) as last_animal
    FROM animal_conditions 
    WHERE emp_id = ?";

$stmt = $conn->prepare($vetSummarySql);
$stmt->bind_param("ii", $vetId, $vetId);
$stmt->execute();
$vetSummaryResult = $stmt->get_result();
$vetSummaryData = $vetSummaryResult->fetch_assoc();
$stmt->close();

// Fetch current health status counts from animals table
$healthStatusSql = "SELECT 
    COUNT(CASE WHEN status = 'well' THEN 1 END) as well_count,
    COUNT(CASE WHEN status = 'sick' THEN 1 END) as sick_count,
    COUNT(CASE WHEN status = 'recovering' THEN 1 END) as recovering_count
    FROM animals 
    WHERE deleted = 0";

$healthStatusResult = $conn->query($healthStatusSql);
$healthStatusData = $healthStatusResult->fetch_assoc();
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
            margin: 20px 0px;
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
            padding: 0px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
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

        .summary-section {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h1>Animal Medical Records</h1>

    <div class="summary-container">
        <div class="summary-section">
            <h3>Current Animal Health Status</h3>
            <ul>
                <li>Well: <span id="well-count"><?php echo $healthStatusData['well_count']; ?></span></li>
                <li>Sick: <span id="sick-count"><?php echo $healthStatusData['sick_count']; ?></span></li>
                <li>Recovering: <span id="recovering-count"><?php echo $healthStatusData['recovering_count']; ?></span></li>
            </ul>
        </div>

        <div class="summary-section">
            <h3>Selected Animal Summary (Last 7 Days)</h3>
            <ul>
                <li>Times Sick: <span id="animal-sick-count">0</span></li>
                <li>Times Well: <span id="animal-well-count">0</span></li>
                <li>Times Recovering: <span id="animal-recovering-count">0</span></li>
                <li>Last Sick Date: <span id="last-sick-date">Never</span></li>
                <li>Last Vet: <span id="last-vet">None</span></li>
            </ul>
        </div>

        <div class="summary-section">
            <h3>Selected Veterinarian Summary (Last 7 Days)</h3>
            <ul>
                <li>Total Reports: <span id="total-reports">0</span></li>
                <li>Last Report Date: <span id="last-report-date">Never</span></li>
                <li>Last Animal Reviewed: <span id="last-animal">None</span></li>
            </ul>
        </div>
    </div>

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

    <script>
        function calculateSummaries(records) {
            // Get the date 7 days ago
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

            // Filter records from the last week
            const lastWeekRecords = records.filter(record => {
                const recordDate = new Date(record.recorded_at);
                return recordDate >= oneWeekAgo;
            });

            // Get selected animal and vet from filters
            const selectedAnimalId = document.getElementById('animal-filter').value;
            const selectedVetId = document.getElementById('vet-filter').value;

            // Calculate selected animal summary for the last week
            let animalSummary = {
                sick_count: 0,
                well_count: 0,
                recovering_count: 0,
                last_sick_date: null,
                last_vet: null
            };

            // Calculate selected vet summary for the last week
            let vetSummary = {
                total_reports: 0,
                last_report_date: null,
                last_animal: null
            };

            if (selectedAnimalId) {
                const animalRecords = lastWeekRecords.filter(r => r.animal_id == selectedAnimalId);
                animalSummary = {
                    sick_count: animalRecords.filter(r => r.health_status === 'sick').length,
                    well_count: animalRecords.filter(r => r.health_status === 'well').length,
                    recovering_count: animalRecords.filter(r => r.health_status === 'recovering').length,
                    last_sick_date: animalRecords
                        .filter(r => r.health_status === 'sick')
                        .sort((a, b) => new Date(b.recorded_at) - new Date(a.recorded_at))[0]?.recorded_at || null,
                    last_vet: animalRecords[0]?.emp_name || null
                };
            }

            if (selectedVetId) {
                const vetRecords = lastWeekRecords.filter(r => r.emp_id == selectedVetId);
                vetSummary = {
                    total_reports: vetRecords.length,
                    last_report_date: vetRecords
                        .sort((a, b) => new Date(b.recorded_at) - new Date(a.recorded_at))[0]?.recorded_at || null,
                    last_animal: vetRecords[0]?.animal_name || null
                };
            }

            return {
                animalSummary,
                vetSummary
            };
        }

        function updateSummaryDisplay(summaries) {
            // Update selected animal summary
            document.getElementById('animal-sick-count').textContent = summaries.animalSummary.sick_count;
            document.getElementById('animal-well-count').textContent = summaries.animalSummary.well_count;
            document.getElementById('animal-recovering-count').textContent = summaries.animalSummary.recovering_count;
            document.getElementById('last-sick-date').textContent = summaries.animalSummary.last_sick_date || 'Never';
            document.getElementById('last-vet').textContent = summaries.animalSummary.last_vet || 'None';

            // Update selected vet summary
            document.getElementById('total-reports').textContent = summaries.vetSummary.total_reports;
            document.getElementById('last-report-date').textContent = summaries.vetSummary.last_report_date || 'Never';
            document.getElementById('last-animal').textContent = summaries.vetSummary.last_animal || 'None';
        }

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
                    // Calculate and update summaries based on the records
                    const summaries = calculateSummaries(data.records);
                    updateSummaryDisplay(summaries);
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