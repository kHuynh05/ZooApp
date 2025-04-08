<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/animals-info.css">
</head>

<body>
    <?php
    // Include database connection
    include '../config/database.php';

    if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST["enclosureType"]) && $_POST["enclosureType"] != "") {
        $selectedEnclosureID = $_POST["enclosureType"];

        $sqlForAnimals = "SELECT species_id, species_name, img FROM species WHERE enclosure_id = ? AND deleted = 0";
        $stmt = $conn->prepare($sqlForAnimals);
        $stmt->bind_param("i", $selectedEnclosureID);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sqlForAnimals = "SELECT species_id, species_name, img FROM species";
        $result = $conn->query($sqlForAnimals);
    }

    // Check if there are any animals in the result
    if ($result->num_rows > 0) {
        // Loop through the results and display each animal
        $animals = [];
        while ($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
    } else {
        // Handle the case if no animals are found
        $animals = ['species_id' => null, 'species_name' => null, 'img' => null];
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
        $enclosures = ['enclosure_id' => null, 'enclosure_name' => null];
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
                    <?php foreach ($enclosures as $enclosure):
                        if ($enclosure['enclosure_id'] != null): ?>

                            <option value="<?php echo $enclosure['enclosure_id']; ?>" <?php echo isset($_POST["enclosureType"]) && ($_POST["enclosureType"] == $enclosure['enclosure_id']) ? 'selected' : ''; ?>><?php echo $enclosure['enclosure_name']; ?></option>

                    <?php endif;
                    endforeach; ?>
                </select>
            </form>
        </div>

        <div class="animal-grid">
            <?php foreach ($animals as $animal):
                if ($animal['species_id'] != null): ?>

                    <a href='animal.php?species_id=<?php echo $animal['species_id']; ?>' class="animal-item">
                        <img src="<?php echo $animal['img']; ?>" alt="<?php echo $animal['species_name']; ?>">
                        <h3><?php echo $animal['species_name']; ?></h3>
                    </a>

            <?php endif;
            endforeach; ?>
        </div>
        <?php include('../includes/footer.php'); ?>

    </div>
</body>

</html>