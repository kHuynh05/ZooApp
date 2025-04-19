// Check for new notifications periodically
function checkNewNotifications() {
    fetch('../scripts/check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.new_notifications) {
                location.reload();
            }
        });
}

// Show assignment modal
function showAssignmentModal(data) {
    document.getElementById('modalReportId').textContent = data.reportId;
    document.getElementById('modalEnclosureName').textContent = data.enclosureName;
    document.getElementById('reportId').value = data.reportId;
    document.getElementById('enclosureId').value = data.enclosureId;
    
    loadCaretakersForEnclosure(data.enclosureId, data.suggestedId);
    
    document.getElementById('assignmentModal').style.display = 'block';
}

// Load caretakers for the enclosure
function loadCaretakersForEnclosure(enclosureId, suggestedId) {
    fetch(`../scripts/get_enclosure_caretakers.php?enclosure_id=${enclosureId}`)
        .then(response => response.json())
        .then(caretakers => {
            const select = document.getElementById('caretakerId');
            select.innerHTML = '<option value="">Choose a caretaker...</option>';
            
            caretakers.forEach(caretaker => {
                const option = document.createElement('option');
                option.value = caretaker.emp_id;
                option.textContent = `${caretaker.emp_name} (${caretaker.ongoing_reports} ongoing reports)`;
                if (caretaker.emp_id === suggestedId) {
                    option.classList.add('suggested');
                    document.getElementById('suggestedCaretakerName').textContent = caretaker.emp_name;
                    document.getElementById('suggestedCaretakerWorkload').textContent = 
                        `Current workload: ${caretaker.ongoing_reports} ongoing reports`;
                }
                select.appendChild(option);
            });

            if (suggestedId) {
                select.value = suggestedId;
                document.getElementById('suggestedCaretakerSection').style.display = 'block';
            } else {
                document.getElementById('suggestedCaretakerSection').style.display = 'none';
            }
        });
}

// Hide assignment modal
function hideAssignmentModal() {
    document.getElementById('assignmentModal').style.display = 'none';
}

// Handle assignment form submission
document.getElementById('assignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../scripts/assign_report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAssignmentModal();
            location.reload();
        } else {
            alert(data.message || 'Failed to assign report');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while assigning the report');
    });
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('assignmentModal');
    if (event.target === modal) {
        hideAssignmentModal();
    }
}

// Check for new notifications every 30 seconds
setInterval(checkNewNotifications, 30000);

function showNewReportForm() {
    document.getElementById('newReportModal').style.display = 'block';
}

function hideNewReportForm() {
    document.getElementById('newReportModal').style.display = 'none';
    document.getElementById('newReportForm').reset();
}

// Remove the duplicate DOMContentLoaded event listener at the bottom and combine all the initialization code into one handler
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for report details
    const reportDetails = document.getElementById('reportDetails');
    const charCount = document.querySelector('.char-count');

    if (reportDetails && charCount) {
        reportDetails.addEventListener('input', function() {
            const remaining = this.value.length;
            charCount.textContent = `${remaining}/200 characters`;
        });
    }

    // Form submission handler for new reports
    const newReportForm = document.getElementById('newReportForm');
    if (newReportForm) {
        newReportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../scripts/submit_manager_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideNewReportForm();
                    location.reload();
                } else {
                    alert(data.message || 'Failed to create report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the report');
            });
        });
    }
});

// Update the window click handler to handle both modals
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Add this new function
function loadCaretakersForNewReport(enclosureId) {
    if (!enclosureId) {
        const select = document.getElementById('reportCaretaker');
        select.innerHTML = '<option value="">First select an enclosure...</option>';
        document.getElementById('newReportCaretakerWorkload').textContent = '';
        return;
    }

    fetch(`../scripts/get_enclosure_caretakers.php?enclosure_id=${enclosureId}`)
        .then(response => response.json())
        .then(caretakers => {
            const select = document.getElementById('reportCaretaker');
            select.innerHTML = '<option value="">Choose a caretaker...</option>';
            
            caretakers.forEach(caretaker => {
                const option = document.createElement('option');
                option.value = caretaker.emp_id;
                option.textContent = `${caretaker.emp_name} (${caretaker.ongoing_reports} ongoing reports)`;
                select.appendChild(option);
            });

            // Add change event listener to show workload
            select.onchange = function() {
                const selectedCaretaker = caretakers.find(c => c.emp_id == this.value);
                const workloadInfo = document.getElementById('newReportCaretakerWorkload');
                if (selectedCaretaker) {
                    workloadInfo.textContent = `Current workload: ${selectedCaretaker.ongoing_reports} ongoing reports`;
                } else {
                    workloadInfo.textContent = '';
                }
            };
        });
} 