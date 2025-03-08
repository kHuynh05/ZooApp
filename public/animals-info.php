<head>
    <link rel="stylesheet" href="../assets/css/homepage.css">
</head>
<?php
// Include database connection
include '../config/database.php';

if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST["enclosureType"]) && $_POST["enclosureType"] != "") {
    $selectedEnclosureID = $_POST["enclosureType"];

    $sql = "SELECT animal_id, animal_name, enclosure_id, FROM animals WHERE enclosure_id = $selectedEnclosureID";

    $result = $conn->query($sql);
} else {
    $sql = "SELECT animal_id, animal_name, enclosure_id, FROM animals";
}


$result = $conn->query($sql);
// Check if there are any events in the result
if ($result->num_rows > 0) {
    // Loop through the results and display each event
    $animals = [];
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
} else {
    // Handle the case if no events are found
    $animals = [];
}

// Close the connection
$conn->close();
?>

<div class="container">
    <?php include('../includes/navbar.php'); ?>
    <h1>Our Animals</h1>

    <div class="filter-container">
        <form method="POST">
            <select name="enclosureType" onchange="this.form.submit()">
                <option value="">All Enclosures</option>
                <?php foreach ($enclosures as $enclosure): ?>
                    <option value="<?php echo $enclosure['enclosure_id']; ?>"><?php echo $enclosure['enclosure_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="animal-grid">
        <?php foreach ($animals as $animal): ?>
            <div class="animal-item">
                <!-- <img src="<?php echo $animal['animal_image']; ?>" alt="<?php echo $animal['animal_name']; ?>"> -->
                <h3><?php echo $animal['animal_name']; ?></h3>
            </div>
        <?php endforeach; ?>
    </div>
</div>