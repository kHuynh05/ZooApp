<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/animals-info.css">
</head>

<body>
    <?php
    // Include database connection
    include '../config/database.php';

    // Get all enclosures first for dropdown and name resolution
    $sqlForEnclosures = "SELECT enclosure_id, enclosure_name FROM enclosures";
    $enclosureResult = $conn->query($sqlForEnclosures);
    $enclosures = [];

    if ($enclosureResult && $enclosureResult->num_rows > 0) {
        while ($row = $enclosureResult->fetch_assoc()) {
            $enclosures[] = $row;
        }
    } else {
        $enclosures = [['enclosure_id' => null, 'enclosure_name' => null]];
    }

    // Check for enclosure selection via POST or GET
    $selectedEnclosureID = null;

    if (
        ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enclosureType"]) && $_POST["enclosureType"] != "") ||
        (isset($_GET["enclosure"]) && $_GET["enclosure"] != "")
    ) {
        $selectedEnclosureID = $_SERVER["REQUEST_METHOD"] == "POST"
            ? $_POST["enclosureType"]
            : $_GET["enclosure"];

        $sqlForAnimals = "SELECT species_id, species_name, img FROM species WHERE enclosure_id = ? AND deleted = 0";
        $stmt = $conn->prepare($sqlForAnimals);
        $stmt->bind_param("i", $selectedEnclosureID); // "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sqlForAnimals = "SELECT species_id, species_name, img FROM species WHERE deleted = 0";
        $result = $conn->query($sqlForAnimals);
    }

    // Collect animal results
    $animals = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
    } else {
        $animals = [['species_id' => null, 'species_name' => null, 'img' => null]];
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
                            <option value="<?php echo $enclosure['enclosure_id']; ?>"
                                <?php
                                $selected = ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enclosureType"]) && $_POST["enclosureType"] == $enclosure['enclosure_id']) ||
                                            (isset($_GET["enclosure"]) && $_GET["enclosure"] == $enclosure['enclosure_id']);
                                echo $selected ? 'selected' : '';
                                ?>>
                                <?php echo htmlspecialchars($enclosure['enclosure_name']); ?>
                            </option>
                    <?php endif;
                    endforeach; ?>
                </select>
            </form>
        </div>

        <div class="animal-grid">
            <?php foreach ($animals as $animal):
                if ($animal['species_id'] != null): ?>
                    <a href='animal.php?species_id=<?php echo $animal['species_id']; ?>' class="animal-item">
                        <img src="<?php echo htmlspecialchars($animal['img']); ?>" alt="<?php echo htmlspecialchars($animal['species_name']); ?>">
                        <h3><?php echo htmlspecialchars($animal['species_name']); ?></h3>
                    </a>
            <?php endif;
            endforeach; ?>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>

</html>
