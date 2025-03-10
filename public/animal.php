<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <!-- Link to your CSS file -->
    <link rel="stylesheet" href="../assets/css/animal.css" />
</head>

<body>

    <!-- Breadcrumb or back link to All Animals -->
    <?php
    include('../includes/navbar.php');
    include '../config/database.php';

    if (isset($_GET['animal_id'])) {
        $animal_id = intval($_GET['animal_id']);

        $query = "SELECT a.animal_name, a.image, a.animal_description, a.fact,
                         e.enclosure_name,
                         s.species_name, s.conservation_status, s.habitat
                  FROM animals AS a, enclosures AS e, species AS s
                  WHERE a.enclosure_id = e.enclosure_id AND a.species_id = s.species_id AND a.animal_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $animal_id);
        $stmt->execute();
        $stmt->bind_result(
            $animal_name,
            $image,
            $animal_description,
            $fact,
            $enclosure_name,
            $species_name,
            $conservation_status,
            $habitat
        );
        $stmt->fetch();
        $stmt->close();

        if ($animal_name) {
            echo '<section class="hero-section">';
            echo '<div class="hero-text">';
            echo '<h1>' . $animal_name . '</h1>';
            echo '<p class="subtitle">' . $animal_description . '</p>';
            echo '<div class="status-tabs">';
            echo '<button class="' . (strtolower($conservation_status) === 'stable' ? 'active' : '') . ' stable">Stable</button>';
            echo '<button class="' . (strtolower($conservation_status) === 'vulnerable' ? 'active' : '') . ' vulnerable">Vulnerable</button>';
            echo '<button class="' . (strtolower($conservation_status) === 'endangered' ? 'active' : '') . ' endangered">Endangered</button>';
            echo '</div>';
            echo '</div>';
            echo '<div class="hero-image">';
            echo '<img src="' . $image . '" alt="' . $animal_name . '" />';
            echo '</div>';
            echo '</section>';

            echo '<section class="facts-section">';
            echo '<h2>Animal Facts</h2>';
            echo '<p><strong>Scientific Name</strong><br />' . $species_name . '</p>';
            echo '<p><strong>Location in the Zoo</strong><br />' . $enclosure_name . '</p>';
            echo '<p><strong>Habitat</strong><br />' . $habitat . '</p>';
            echo '<p><strong>Cool Animal Fact</strong><br />' . $fact . '</p>';
            echo '</section>';
        } else {
            echo "<p>Animal not found.</p>";
        }
        $conn->close();
    } else {
        echo "<p>No animal ID provided.</p>";
    }
    ?>
    <?php include('../includes/footer.php'); ?>


</body>

</html>