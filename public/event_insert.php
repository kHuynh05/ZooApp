//admin checker logic!! GOES HERE

<?php
// Include database connection
include '../config/database.php';

$sqlForEventFields = "SELECT 
    cols.COLUMN_NAME,
    cols.DATA_TYPE,
    cols.IS_NULLABLE,
    cols.COLUMN_KEY,
    refs.REFERENCED_TABLE_NAME,
    refs.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS AS cols
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS refs
    ON cols.TABLE_SCHEMA = refs.TABLE_SCHEMA
    AND cols.TABLE_NAME = refs.TABLE_NAME
    AND cols.COLUMN_NAME = refs.COLUMN_NAME
    AND refs.REFERENCED_TABLE_NAME IS NOT NULL
WHERE cols.TABLE_NAME = 'EVENTS'
  AND cols.TABLE_SCHEMA = 'ZOOAPP';
";

$result = $conn->query($sqlForAnimals);


// Check if there are any animals in the result
if ($result->num_rows > 0) {
    // Loop through the results and display each animal
    $eventFields = [];
    while ($row = $result->fetch_assoc()) {
        $eventFields[] = $row;
    }
} else {
    // Handle the case if failure to get event field
    $eventFields = ['event_id' => null];
}

?>

<form action="/insert" method="POST" class="insert-form">
    <h2>Insert New Item</h2>

    <!-- Field Group: Example Input -->
    <div class="form-group">
        <label for="field1">Field Label 1</label>
        <input type="text" id="field1" name="field1" placeholder="Enter value" required>
    </div>

    <!-- Field Group: Example Dropdown -->
    <div class="form-group">
        <label for="field2">Field Label 2</label>
        <select id="field2" name="field2" required>
            <option value="">Select an option</option>
            <option value="value1">Option 1</option>
            <option value="value2">Option 2</option>
        </select>
    </div>

    <!-- More fields can be added/removed here as needed -->

    <button type="submit">Submit</button>
</form>