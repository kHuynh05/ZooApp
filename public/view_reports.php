<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

// Verify manager access
if (!in_array('view_reports', $allowed_actions)) {
    header("Location: ../public/employeePortal.php");
    exit();
}

// Fetch customer contacts (keeping existing query)
$contacts_query = "SELECT * FROM contact ORDER BY created_at DESC";
$contacts_result = $conn->query($contacts_query);

// Fetch all reports with related information (new query)
$reports_query = "SELECT 
    r.*,
    e1.emp_name as creator_name,
    e2.emp_name as assigned_name,
    enc.enclosure_name,
    mn.notif_id,
    mn.suggested_id,
    mn.handled,
    (SELECT COUNT(*) 
     FROM reports r2 
     WHERE r2.assigned_id = r.assigned_id 
     AND r2.status = 'ongoing') as assignee_ongoing_reports
    FROM reports r
    LEFT JOIN employees e1 ON r.creator_id = e1.emp_id
    LEFT JOIN employees e2 ON r.assigned_id = e2.emp_id
    LEFT JOIN enclosures enc ON r.enclosure_id = enc.enclosure_id
    LEFT JOIN manager_notifications mn ON r.report_id = mn.report_id
    ORDER BY 
        CASE WHEN mn.handled = 0 THEN 0 ELSE 1 END,
        r.report_datetime DESC";
$reports_result = $conn->query($reports_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports - Zoo Management</title>
    <link rel="stylesheet" href="../assets/css/view_reports.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>

<body>
    <div class="main-container">
        <h1>Reports Management</h1>

        <!-- Customer Contacts Section (keeping existing section) -->
        <section class="reports-section">
            <h2>Customer Contacts</h2>
            <div class="reports-grid">
                <?php while ($contact = $contacts_result->fetch_assoc()): ?>
                    <div class="report-card">
                        <div class="report-header">
                            <h3><?php echo htmlspecialchars($contact['title']); ?></h3>
                            <span class="report-date"><?php echo date('F j, Y g:i A', strtotime($contact['created_at'])); ?></span>
                        </div>
                        <div class="report-content">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($contact['message']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Staff Reports Section (new section) -->
        <section class="reports-section">
            <h2>Staff Reports</h2>
            <div class="reports-list">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Report #</th>
                            <th>DateTime</th>
                            <th>Enclosure</th>
                            <th>Created By</th>
                            <th>Assigned To</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($report = $reports_result->fetch_assoc()): ?>
                            <tr class="<?php echo (!$report['handled'] && $report['notif_id']) ? 'needs-attention' : ''; ?>">
                                <td><?php echo $report['report_id']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($report['report_datetime'])); ?></td>
                                <td><?php echo htmlspecialchars($report['enclosure_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['creator_name']); ?></td>
                                <td>
                                    <?php if ($report['assigned_id']): ?>
                                        <?php echo htmlspecialchars($report['assigned_name']); ?>
                                        <span class="workload-badge">
                                            <?php echo $report['assignee_ongoing_reports']; ?> ongoing
                                        </span>
                                    <?php else: ?>
                                        <span class="unassigned">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="details-cell">
                                    <?php echo htmlspecialchars($report['report_details']); ?>
                                    <?php if ($report['status'] === 'finished'): ?>
                                        <br><small class="resolution-note">
                                            Resolution: <?php echo htmlspecialchars($report['resolution_note']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$report['assigned_id']): ?>
                                        <button class="assign-btn"
                                            onclick="showAssignmentModal(<?php
                                                                            echo htmlspecialchars(json_encode([
                                                                                'reportId' => $report['report_id'],
                                                                                'enclosureId' => $report['enclosure_id'],
                                                                                'enclosureName' => $report['enclosure_name'],
                                                                                'suggestedId' => $report['suggested_id']
                                                                            ]));
                                                                            ?>)">
                                            Assign Caretaker
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="modal">
        <div class="modal-content">
            <h3>Assign Report #<span id="modalReportId"></span></h3>
            <p>Enclosure: <span id="modalEnclosureName"></span></p>

            <div class="suggested-caretaker" id="suggestedCaretakerSection">
                <h4>Suggested Caretaker:</h4>
                <p id="suggestedCaretakerName"></p>
                <p class="workload-info" id="suggestedCaretakerWorkload"></p>
            </div>

            <form id="assignmentForm">
                <input type="hidden" id="reportId" name="report_id">
                <input type="hidden" id="enclosureId" name="enclosure_id">

                <div class="form-group">
                    <label for="caretakerId">Select Caretaker:</label>
                    <select id="caretakerId" name="assigned_id" required>
                        <option value="">Choose a caretaker...</option>
                    </select>
                    <p class="workload-info" id="selectedCaretakerWorkload"></p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Assign Report</button>
                    <button type="button" class="btn-cancel" onclick="hideAssignmentModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../scripts/view_reports.js"></script>
</body>

</html>