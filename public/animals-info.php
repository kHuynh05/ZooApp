<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/homepage.css">
</head>

<body>
    <?php
    // Include database connection
    include '../config/database.php';

    if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST["enclosureType"]) && $_POST["enclosureType"] != "") {
        $selectedEnclosureID = $_POST["enclosureType"];

        $sqlForAnimals = "SELECT animal_id, animal_name, FROM animals WHERE enclosure_id = $selectedEnclosureID";

        $result = $conn->query($sqlForAnimals);
    } else {
        $sqlForAnimals = "SELECT animal_id, animal_name, FROM animals";
    }


    $result = $conn->query($sqlForAnimals);
    // Check if there are any animals in the result
    if ($result->num_rows > 0) {
        // Loop through the results and display each animal
        $animals = [];
        while ($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
    } else {
        // Handle the case if no animals are found
        $animals = ['No animals found'];
    }

    $sqlForEnclosures = "SELECT enclosure_id, enclosure_name FROM enclosures";

    $result = $conn->query($sqlForEnclosures);
    // Check if there are any enclosures in the result
    if ($result->num_rows > 0) {
        // Loop through the results and display each enclosure
        $enclosures = [];
        while ($row = $result->fetch_assoc()) {
            $enclosures[] = $row;
        }
    } else {
        // Handle the case if no enclosures are found
        $enclosures = ['No enclosures found'];
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
</body>

</html>