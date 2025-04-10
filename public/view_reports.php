<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

// Fetch customer contacts
$contacts_query = "SELECT * FROM contact ORDER BY created_at DESC";
$contacts_result = $conn->query($contacts_query);

// Fetch caretaker/veterinarian reports
$reports_query = "SELECT * FROM reports ORDER BY report_datetime DESC";
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

        <!-- Customer Contacts Section -->
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

        <!-- Caretaker/Veterinarian Reports Section -->
        <section class="reports-section">
            <h2>Staff Reports</h2>
            <div class="reports-grid">
                <?php while ($report = $reports_result->fetch_assoc()): ?>
                    <div class="report-card">
                        <div class="report-header">
                            <h3>Report #<?php echo $report['report_id']; ?></h3>
                            <span class="report-date"><?php echo date('F j, Y g:i A', strtotime($report['report_datetime'])); ?></span>
                        </div>
                        <div class="report-content">
                            <p><?php echo htmlspecialchars($report['report_details']); ?></p>
                        </div>
                        <div class="report-actions">
                            <button class="toggle-response-btn <?php echo $report['has_been_responded'] ? 'responded' : ''; ?>"
                                data-report-id="<?php echo $report['report_id']; ?>"
                                data-current-status="<?php echo $report['has_been_responded']; ?>">
                                <?php echo $report['has_been_responded'] ? 'Mark as Unresponded' : 'Mark as Responded'; ?>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-response-btn');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportId = this.dataset.reportId;
                    const currentStatus = this.dataset.currentStatus;
                    const newStatus = currentStatus === '1' ? '0' : '1';

                    fetch('../scripts/mark_report_responded.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `report_id=${reportId}&new_status=${newStatus}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.dataset.currentStatus = newStatus;
                                this.classList.toggle('responded');
                                this.textContent = newStatus === '1' ? 'Mark as Unresponded' : 'Mark as Responded';
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>

</html>