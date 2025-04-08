<?php
include '../scripts/employeeRole.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Initialize filter
$role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : 'all';

// Base query to get active employees
$query = "SELECT emp_id, emp_name, emp_email, starting_day, sex, role 
         FROM employees 
         WHERE active = 1";

// Add role filter if specific role is selected
if ($role_filter !== 'all') {
    $query .= " AND role = ?";
}

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($role_filter !== 'all') {
    $stmt->bind_param("s", $role_filter);
}
$stmt->execute();
$result = $stmt->get_result();

// If it's an AJAX request, only return the table body content
if ($isAjax) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['emp_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['emp_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['emp_email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['starting_day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sex']) . "</td>";
        $role_display = [
            'manager' => 'Manager',
            'shop' => 'Shopkeeper',
            'care' => 'Caretaker',
            'vet' => 'Veterinarian'
        ];
        echo "<td>" . htmlspecialchars($role_display[$row['role']] ?? $row['role']) . "</td>";
        echo "</tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/view_employees.css">
</head>
<body>
    <div class="filter-container">
        <label for="role_filter">Filter by Role:</label>
        <select name="role_filter" id="role_filter">
            <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Employees</option>
            <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>>Managers</option>
            <option value="shop" <?php echo $role_filter === 'shop' ? 'selected' : ''; ?>>Shopkeepers</option>
            <option value="care" <?php echo $role_filter === 'care' ? 'selected' : ''; ?>>Caretakers</option>
            <option value="vet" <?php echo $role_filter === 'vet' ? 'selected' : ''; ?>>Veterinarians</option>
        </select>
    </div>

    <div class="employees-list">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Starting Day</th>
                    <th>Gender</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['emp_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['emp_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['starting_day']); ?></td>
                        <td><?php echo htmlspecialchars($row['sex']); ?></td>
                        <td><?php
                            $role_display = [
                                'manager' => 'Manager',
                                'shop' => 'Shopkeeper',
                                'care' => 'Caretaker',
                                'vet' => 'Veterinarian'
                            ];
                            echo htmlspecialchars($role_display[$row['role']] ?? $row['role']);
                        ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.getElementById('role_filter').addEventListener('change', function() {
        const selectedRole = this.value;
        
        fetch(`view_employees.php?role_filter=${selectedRole}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('employeeTableBody').innerHTML = html;
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