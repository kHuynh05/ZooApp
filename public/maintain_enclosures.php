<?php
include '../scripts/employeeRole.php';
?>
<link rel="stylesheet" href="../assets/css/maintain.css">
<div class="content-section">
    <h2>Enclosure Maintenance Management</h2>

    <!-- Assigned Enclosures Status Section -->
    <div class="enclosure-status-section">
        <h3>Your Assigned Enclosures</h3>
        <?php
        // Get caretaker's assigned enclosures with their status
        $emp_id = $_SESSION['emp_id'];
        $sql = "SELECT e.enclosure_id, e.enclosure_name, e.status 
                FROM enclosures e 
                JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                WHERE c.emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='enclosure-grid'>";
            while ($row = $result->fetch_assoc()) {
                $statusClass = $row['status'] == 'closed' ? 'status-closed' : 'status-open';
                echo "<div class='enclosure-card' data-enclosure-id='{$row['enclosure_id']}'>";
                echo "<h4>" . htmlspecialchars($row['enclosure_name']) . "</h4>";
                echo "<p class='status {$statusClass}'>Status: " . ucfirst(htmlspecialchars($row['status'])) . "</p>";
                echo "<div class='action-buttons'>";
                echo "<button onclick='toggleEnclosureStatus({$row['enclosure_id']}, \"{$row['status']}\")' class='btn-toggle'>";
                echo $row['status'] == 'closed' ? 'Open Enclosure' : 'Close Enclosure';
                echo "</button>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
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
        $sql = "SELECT a.animal_id, a.animal_name, s.species_name, ac.mood, ac.health_status, ac.recorded_at
                FROM animals a
                JOIN species s ON a.species_id = s.species_id
                JOIN enclosures e ON a.enclosure_id = e.enclosure_id
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

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='animal-conditions-table'>";
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>Animal</th>";
            echo "<th>Species</th>";
            echo "<th>Mood</th>";
            echo "<th>Health Status</th>";
            echo "<th>Last Updated</th>";
            echo "</tr></thead><tbody>";

            while ($row = $result->fetch_assoc()) {
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

    <!-- Maintenance Reports Section -->
    <div class="maintenance-reports-section">
        <h3>Maintenance Reports</h3>
        <div class="report-actions">
            <button onclick="showNewReportForm()" class="btn-primary">Create New Report</button>
        </div>

        <!-- New Report Form (Initially Hidden) -->
        <div id="newReportForm" class="report-form hidden">
            <h4>New Maintenance Report</h4>
            <form id="maintenanceReportForm">
                <div class="form-group">
                    <label for="report_details">Report Details:</label>
                    <textarea name="report_details" rows="4" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Submit Report</button>
                    <button type="button" onclick="hideNewReportForm()" class="btn-cancel">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Reports List -->
        <div class="reports-list">
            <h4>Recent Maintenance Reports</h4>
        </div>
    </div>
</div>

<script>
    function toggleEnclosureStatus(enclosureId, currentStatus) {
        const newStatus = currentStatus === 'closed' ? 'open' : 'closed';

        fetch('../scripts/toggle_enclosure_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `enclosure_id=${enclosureId}&new_status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the enclosure card instead of reloading
                    const card = document.querySelector(`[data-enclosure-id="${enclosureId}"]`);
                    if (card) {
                        const statusElement = card.querySelector('.status');
                        const buttonElement = card.querySelector('.btn-toggle');

                        // Update status class and text
                        statusElement.className = `status status-${newStatus}`;
                        statusElement.textContent = `Status: ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`;

                        // Update button text
                        buttonElement.textContent = newStatus === 'closed' ? 'Open Enclosure' : 'Close Enclosure';
                    }
                    // Refresh the reports list to show the new status change report
                    loadMaintenanceReports();
                } else {
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the status');
            });
    }

    function loadMaintenanceReports() {
        fetch('../scripts/get_maintenance_reports.php')
            .then(response => response.json())
            .then(reports => {
                const reportsList = document.querySelector('.reports-list');
                if (reports.length === 0) {
                    reportsList.innerHTML = '<h4>Recent Maintenance Reports</h4><p>No maintenance reports found.</p>';
                    return;
                }

                let html = '<h4>Recent Maintenance Reports</h4><table>';
                html += '<thead><tr>';
                html += '<th style="width: 200px;">DateTime</th>';
                html += '<th>Details</th>';
                html += '<th style="width: 150px;">Status</th>';
                html += '</tr></thead>';
                html += '<tbody>';

                reports.forEach(report => {
                    // Format the datetime
                    const date = new Date(report.report_datetime);
                    const formattedDate = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                    const formattedTime = date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const statusClass = report.has_been_responded ? 'status-responded' : 'status-pending';
                    const statusText = report.has_been_responded ? 'Responded' : 'Pending';

                    html += `<tr>
                        <td class="datetime-column">${formattedDate}<br>${formattedTime}</td>
                        <td class="details-column">${report.report_details}</td>
                        <td><span class="report-status ${statusClass}">${statusText}</span></td>
                    </tr>`;
                });

                html += '</tbody></table>';
                reportsList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                const reportsList = document.querySelector('.reports-list');
                reportsList.innerHTML = '<h4>Recent Maintenance Reports</h4><p class="error">Failed to load reports.</p>';
            });
    }

    function showNewReportForm() {
        document.getElementById('newReportForm').classList.remove('hidden');
    }

    function hideNewReportForm() {
        document.getElementById('newReportForm').classList.add('hidden');
    }

    document.getElementById('maintenanceReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../scripts/submit_maintenance_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideNewReportForm();
                    loadMaintenanceReports();
                }
            });
    });

    // Remove the updateAnimalCondition function since caretakers can't update conditions

    // Load reports when page loads
    document.addEventListener('DOMContentLoaded', loadMaintenanceReports);
</script>