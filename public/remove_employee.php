<?php
include '../scripts/employeeRole.php';

// Check if this is an AJAX request for deletion
if (isset($_POST['emp_id']) && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $emp_id = $_POST['emp_id'];
    $update_query = "UPDATE employees SET active = 0 WHERE emp_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $emp_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}

// Search functionality
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Query to get active employees with search
$query = "SELECT emp_id, emp_name, emp_email, starting_day, sex, role, date_of_birth, ssn, pay_RATE 
         FROM employees 
         WHERE active = 1 
         AND emp_name LIKE ?
         ORDER BY emp_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/view_employees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            margin: 20px auto;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items:flex-end;
            gap: 5px;
            max-width: 400px;
        }
        
        #searchInput {
            padding: 8px 12px;
            width: 4000px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            height: 35px;
            box-sizing: border-box;
            align-items: flex-end;
        }

        #searchButton, #backButton {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            height: 35px;
            box-sizing: border-box;
        }

        #backButton {
            background-color: #666;
            display: none;
        }

        #searchButton:hover {
            background-color: #45a049;
        }

        #backButton:hover {
            background-color: #555;
        }

        .delete-icon {
            color: red;
            cursor: pointer;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }

        .delete-icon:hover {
            transform: scale(1.2);
        }

        .employees-list {
            overflow-x: auto;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }

        .modal-buttons {
            margin-top: 20px;
        }

        .modal-buttons button {
            margin: 0 10px;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .confirm-btn {
            background-color: red;
            color: white;
            border: none;
        }

        .cancel-btn {
            background-color: #ccc;
            border: none;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search by employee name...">
        <button id="searchButton">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            Search
        </button>
        <button id="backButton">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Back
        </button>
    </div>

    <div class="employees-list">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Starting Day</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Date of Birth</th>
                    <th>SSN</th>
                    <th>Pay Rate</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="employee-row" data-emp-id="<?php echo htmlspecialchars($row['emp_id']); ?>">
                        <td>
                            <i class="fas fa-trash-alt delete-icon" title="Delete employee"></i>
                        </td>
                        <td><?php echo htmlspecialchars($row['emp_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['emp_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['starting_day']); ?></td>
                        <td><?php echo htmlspecialchars($row['sex']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                        <td><?php echo htmlspecialchars($row['ssn']); ?></td>
                        <td><?php echo htmlspecialchars($row['pay_RATE']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p>Are you sure to delete this employee?</p>
            <div class="modal-buttons">
                <button class="confirm-btn" id="confirmDelete">Confirm</button>
                <button class="cancel-btn" id="cancelDelete">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            
            if (searchTerm.trim() !== '') {
                document.getElementById('backButton').style.display = 'flex';
            }
            
            fetch(`remove_employee.php?search=${encodeURIComponent(searchTerm)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.getElementById('employeeTableBody');
                document.getElementById('employeeTableBody').innerHTML = newTbody.innerHTML;
                
                attachRowEventListeners();
            })
            .catch(error => console.error('Error:', error));
        }

        document.getElementById('searchButton').addEventListener('click', performSearch);
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        document.getElementById('backButton').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            this.style.display = 'none';
            performSearch();
        });

        function attachRowEventListeners() {
            document.querySelectorAll('.delete-icon').forEach(icon => {
                icon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    employeeToDelete = this.closest('.employee-row');
                    modal.style.display = 'block';
                });
            });
        }

        // Initial attachment of event listeners
        attachRowEventListeners();

        // Modal functionality
        const modal = document.getElementById('confirmModal');
        let employeeToDelete = null;

        document.getElementById('cancelDelete').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.getElementById('confirmDelete').addEventListener('click', () => {
            const empId = employeeToDelete.dataset.empId;
            
            fetch('remove_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `emp_id=${empId}&action=delete`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    employeeToDelete.remove();
                }
                modal.style.display = 'none';
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>