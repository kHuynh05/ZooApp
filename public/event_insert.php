//admin checker logic!!

<?php

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