<!-- 
 
This delivers the HTML Form to allow for animal inserting
Validation logic will be within adminPortal.php 

-->

<head>
    <link rel="stylesheet" href="../assets/css/insert-form.css">
</head>

<form action="adminPortal.php" method="POST" class="insert-form" enctype="multipart/form-data">
    <h2>Insert New Animal</h2>

    <input type="text" id="field-name" name="animal_name" placeholder="Enter Name!" required>

    <input type="text" id="field-name" name="Sex" placeholder="Enter Sex!" required>

    <input type="text" id="field-name" name="fact" placeholder="Enter Fact!" required>


    <input type="text" id="field-location" name="enclosure" placeholder="Enter Enclosure!" required>

    <input type="text" id="field-description" name="animal_description" placeholder="Enter Description!" required>


    <label for="start-date">Select Start Date:</label>
    <input type="datetime-local" id="start-date" name="date_of_rescue" placeholder="Enter Date and Time!" required>

    <label for="end-time">Select End Date & Time:</label>
    <input type="datetime-local" id="end-time" name="date_of_birth" placeholder="Enter Date and Time!" required>

    <label for="event-picture">Upload Picture:</label>
    <input type="file" id="event-picture" name="picture" accept="image/*" required>

    <button type="submit">Submit</button>
</form>