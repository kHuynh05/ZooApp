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
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p class='warning'>You have no assigned enclosures. Please contact your supervisor.</p>";
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
                    <label for="enclosure_id">Enclosure:</label>
                    <select name="enclosure_id" required>
                        <?php
                        // Get caretaker's assigned enclosures
                        $sql = "SELECT e.enclosure_id, e.enclosure_name 
                                FROM enclosures e 
                                JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
                                WHERE c.emp_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_SESSION['emp_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['enclosure_id'] . "'>" .
                                htmlspecialchars($row['enclosure_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
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

        <!-- Resolution Form (Initially Hidden) -->
        <div id="resolutionForm" class="report-form hidden">
            <h4>Complete Report</h4>
            <form id="reportResolutionForm">
                <input type="hidden" name="report_id" id="resolution_report_id">
                <div class="form-group">
                    <label for="resolution_note">Resolution Details:</label>
                    <textarea name="resolution_note" id="resolution_note" rows="4" maxlength="500" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Mark as Completed</button>
                    <button type="button" onclick="hideResolutionForm()" class="btn-cancel">Cancel</button>
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
    function loadMaintenanceReports() {
        fetch('../scripts/get_maintenance_reports.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const reportsList = document.querySelector('.reports-list');

                // Check if the request was successful
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load reports');
                }

                const reports = data.reports || [];

                if (reports.length === 0) {
                    reportsList.innerHTML = `
                        <h4>Recent Maintenance Reports</h4>
                        <div class="empty-state">
                            <p>No maintenance reports found.</p>
                            <p class="empty-state-hint">Click "Create New Report" above to submit a maintenance report for your assigned enclosures.</p>
                        </div>`;
                    return;
                }

                let html = '<h4>Recent Maintenance Reports</h4><table>';
                html += '<thead><tr>';
                html += '<th style="width: 200px;">DateTime</th>';
                html += '<th>Details</th>';
                html += '<th style="width: 150px;">Status</th>';
                html += '<th style="width: 150px;">Actions</th>';
                html += '</tr></thead>';
                html += '<tbody>';

                reports.forEach(report => {
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

                    const statusClass = report.status === 'finished' ? 'status-completed' : 'status-ongoing';
                    const statusText = report.status === 'finished' ? 'Completed' : 'Ongoing';

                    html += `<tr>
                        <td class="datetime-column">${formattedDate}<br>${formattedTime}</td>
                        <td class="details-column">
                            ${report.report_details}
                            ${report.status === 'finished' ? 
                              `<br><small class="resolution-note">Resolution: ${report.resolution_note || 'None'}</small>` : 
                              ''}
                        </td>
                        <td><span class="report-status ${statusClass}">${statusText}</span></td>
                        <td>`;

                    // Only show complete button if report is assigned to current user and is ongoing
                    if (report.status === 'ongoing' && report.assigned_id === <?php echo $_SESSION['emp_id']; ?>) {
                        html += `<button onclick="showResolutionForm(${report.report_id})" 
                                        class="btn-complete">Complete Report</button>`;
                    }

                    html += `</td></tr>`;
                });

                html += '</tbody></table>';
                reportsList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                const reportsList = document.querySelector('.reports-list');
                reportsList.innerHTML = `
                    <h4>Recent Maintenance Reports</h4>
                    <div class="error-state">
                        <p>Failed to load reports: ${error.message}</p>
                        <button onclick="loadMaintenanceReports()" class="btn-retry">Retry</button>
                    </div>`;
            });
    }

    function showNewReportForm() {
        document.getElementById('newReportForm').classList.remove('hidden');
    }

    function hideNewReportForm() {
        document.getElementById('newReportForm').classList.add('hidden');
    }

    function showResolutionForm(reportId) {
        document.getElementById('resolution_report_id').value = reportId;
        document.getElementById('resolutionForm').classList.remove('hidden');
    }

    function hideResolutionForm() {
        document.getElementById('resolutionForm').classList.add('hidden');
        document.getElementById('reportResolutionForm').reset();
    }

    // Update the form submission handler
    document.getElementById('maintenanceReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('creator_id', <?php echo $_SESSION['emp_id']; ?>);
        formData.append('status', 'ongoing');

        fetch('../scripts/submit_maintenance_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideNewReportForm();
                    this.reset();
                    loadMaintenanceReports();
                } else {
                    alert(data.message || 'Failed to submit report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the report');
            });
    });

    // Update the resolution form submission handler in maintain_enclosures.php
    document.getElementById('reportResolutionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Log the data being sent (for debugging)
        console.log('Submitting resolution:', {
            report_id: formData.get('report_id'),
            resolution_note: formData.get('resolution_note')
        });

        fetch('../scripts/complete_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideResolutionForm();
                    this.reset();
                    loadMaintenanceReports();
                } else {
                    alert(data.message || 'Failed to complete report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while completing the report');
            });
    });

    // Load reports when page loads
    document.addEventListener('DOMContentLoaded', loadMaintenanceReports);
</script>