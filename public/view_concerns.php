<?php
include '../config/database.php';

// Query to get all contact reports ordered by contact_id (most recent first)
$sql = "SELECT * FROM contact ORDER BY contact_id DESC";
$result = $conn->query($sql);
?>

<div class="content-section">
    <h2>Customer Contact Reports</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Title</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['contact_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="message-cell"><?php echo htmlspecialchars($row['message']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="no-data">No contact reports found.</p>
    <?php endif; ?>
</div>

<style>
.content-section {
    padding: 20px;
    max-width: 100%;
    overflow-x: auto;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9em;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.data-table tr:hover {
    background-color: #f5f5f5;
}

.message-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #666;
}

@media (max-width: 768px) {
    .data-table {
        font-size: 0.8em;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 10px;
    }
    
    .message-cell {
        max-width: 200px;
    }
}
</style>
