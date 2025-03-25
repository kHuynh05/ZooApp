<!-- 
 
This delivers the HTML Form to allow for event inserting
Validation logic will be within adminPortal.php 

-->

<head>
    <link rel="stylesheet" href="../assets/css/insert-form.css">
</head>

<form action="adminPortal.php" method="POST" class="insert-form" enctype="multipart/form-data">
    <h2>Insert New Event</h2>

    <input type="text" id="field-name" name="event_name" placeholder="Enter Name!" required>

    <input type="text" id="field-location" name="location" placeholder="Enter Location!" required>

    <input type="text" id="field-description" name="description" placeholder="Enter Description!" required>


    <label for="start-date">Select Start Date:</label>
    <input type="datetime-local" id="start-date" name="event_date" placeholder="Enter Date and Time!" required>

    <label for="end-time">Select End Date & Time:</label>
    <input type="datetime-local" id="end-time" name="ending_time" placeholder="Enter Date and Time!" required>

    <label for="event-picture">Upload Picture:</label>
    <input type="file" id="event-picture" name="picture" accept="image/*" required>

    <button type="submit">Submit</button>
</form>