// Check for new notifications periodically
function checkNewNotifications() {
    fetch('check_notifications.php')
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
    fetch(`get_enclosure_caretakers.php?enclosure_id=${enclosureId}`)
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

    fetch('assign_report.php', {
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