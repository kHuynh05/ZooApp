<head>
    <link rel = "stylesheet" href = "../assets/css/adopt.css">
</head>
<?php
//Include the database connection
include '../config/database.php';

$sql = "SELECT species_name, img, plush FROM species";
$result = $conn->query($sql);
$animals = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
}

$animals_info = [];

foreach ($animals as $animal) {
    $name = $animal['species_name'];
    $animals_info[$name] = [
        'name' => $animal['species_name'],
        'image' => $animal['img'],
        'species_plush' => $animal['plush']
    ];
}

?>

<div class = "container">
    <?php include('../includes/navbar.php');?>
    <div class = "homepage">
        <div class = "frontpage">
            <div class = "frontpage-content">
                <?php
                $species_name = $animal['species_name'];
                $default_image = '../assets/images/default-enclosure.jpg';
                $default_plush = 'A specially designed habitat providing a natural environment for our animals.';

                $image = isset($animals_info[$species_name]) ? $animals_info[$species_name]['image'] : $default_img;
                $plush = isset($animals_info[$species_name]) ? $animals_info[$species_name]['plush'] : $default_plush;
                ?>
                <h1 class = "frontpage-main"><b>Adopt an Animal at Zootopia!</b></h1>
                <span class = "adopt-info">At <b>Zootopia</b>, we believe that every animal deserves love, care, and a thriving habitat. 
                By adopting an animal, you are directly contributing to their well-being, helping us provide nutritious food, 
                veterinary care, enrichment activities, and a safe environment.</span>
                <span class = "adopt-info">Why Adopt?</span>
                <span class = "adopt-info">
                    <ul>
                        <li>Support conservation efforts and help protect endangered species.</li>
                        <li>Provide top-quality care for your favorite animal.</li>
                        <li>Receive an exclusive adoptation certificate and updates about your adopted animal.</li>
                        <li>Make a meaningful impact while creating a special bond.</li>
                    </ul>
                </span>
                <span class = "adopt-info">Your symbolic adoption helps us continue our mission of wildlife conservation and education.</span>
            </div>
        </div>
        <div class = "animal-title">
                <span class = "animal-header">CHOOSE YOUR ANIMAL TO ADOPT</span>
        </div>
        <div class = "animals">
            <?php
            ?>
            <?php foreach ($animals as $index => $animal): ?>
            <div class="animal-card">
                <input type="radio" name="animal-toggle" id="toggle-<?php echo $index; ?>">
                <label for="toggle-<?php echo $index; ?>">
                    <img class="responsive-circle" src="<?php echo $animal['img']; ?>" alt="<?php echo $animal['species_name']; ?>">
                    <span class = "animal-label"><?php echo $animal['species_name'];?></span>
                </label>
                <div class="dropdown-content">
                    <span class="close-btn" onclick="document.getElementById('toggle-<?php echo $index; ?>').checked = false;">×</span>
                    <p>Plushie included only in Guardian and Protector package</p>
                    <img class="plush-image" src="<?php echo $animal['plush']; ?>" alt="<?php echo $animal['species_name']; ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <?php
            $desc = "Plush included in Guardians and Protectors package";
            ?>

            <div class = "dropdown-content">
                <a href="adopt.php" class = "close-btn">x</a>  
                <div class = "dropdown-body">
                    <p>Plush included in Guardians and Protectors package</p>       
                    <img src = "<?php echo htmlspecialchars($image); ?>" class = "dropdown-img">
               </div>
              </div>
        <div class = "packet-title">
            <span class = "packet-head">FIVE LEVELS TO CHOOSE FROM</span>
        </div>
        <div class = "packet">
            <div class = "friends">
                <span class = "packet-header">Friend - $30</span>
                <span class = "packet-info">
                    <ul class = "pack-list">
                        <li>$6 shipping and handling</li>
                        <li>Adoption Certificate</li>
                        <li>Animal Fact Sheet</li>
                        <li>5x7 Photo of Adopted Animal</li>
                        <li>Zootopia E-Newsletter</li>
                    </ul>
                </span>
                <a href = "donation_form.php" class = "more">More</a>
            </div>
            <div class = "guardian">
                <span class = "packet-header">Guardian - $60</span>
                <span class = "packet-info">
                    <ul class = "pack-list">
                        <li>$10 shipping and handling</li>
                        <li>Adoption Certificate</li>
                        <li>Animal Fact Sheet</li>
                        <li>5x7 Photo of Adopted Animal</li>
                        <li>Zootopia E-Newsletter</li>
                        <li>Animal Plush Toy</li>
                    </ul>
                </span>
                <a href = "donation_form.php" class = "more">More</a>
            </div>
            <div class = "protector">
                <span class = "packet-header">Protector - $100</span>
                <span class = "packet-info">
                    <ul class = "pack-list">
                        <li>$10 shipping and handling</li>
                        <li>Adoption Certificate</li>
                        <li>Animal Fact Sheet</li>
                        <li>5x7 Photo of Adopted Animal</li>
                        <li>Zootopia E-Newsletter</li>
                        <li>Animal Plush Toy</li>
                        <li>Two Zootopia Day Passes</li>
                    </ul>
                </span>
                <a href = "donation_form.php" class = "more">More</a>
            </div>
            <div class = "classroom-package">
                <span class = "packet-header">Classroom Package - $120</span>
                <span class = "packet-info">
                    <ul class = "pack-list">
                        <li>$6 shipping and handling</li>
                        <li>(25) Adoption Certificate</li>
                        <li>(25) Animal Fact Sheet</li>
                        <li>5x7 Photo of Adopted Animal</li>
                        <li>Zootopia E-Newsletter</li>
                        <li>Animal Plush Toy</li>
                    </ul>
                </span>
                <a href = "donation_form.php" class = "more">More</a>
            </div>
            <div class = "birthday-package">
                <span class = "packet-header">Birthday Package - $220</span>
                <span class = "packet-info">
                    <ul class = "pack-list">
                        <li>$10 shipping and handling</li>
                        <li>Adoption Certificate</li>
                        <li>Animal Fact Sheet</li>
                        <li>5x7 Photo of Adopted Animal</li>
                        <li>Zootopia E-Newsletter</li>
                        <li>Animal Plush Toy</li>
                        <li>Four Zootopia Day Passes</li>
                        <li>Zootopia Birthday Button</li>
                        <li>A personalized birthday video (20-30 sec) featuring the adopted animal and a zookeeper!</li>
                    </ul>
                </span>
                <a href = "donation_form.php" class = "more">More</a>
            </div>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>