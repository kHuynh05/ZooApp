<head>
    <link rel = "stylesheet" href = "../assets/css/adopt.css">
</head>
<?php
//Include the database connection
include '../config/database.php';

?>

<div class = "container">
    <?php include('../includes/navbar.php');?>
    <div class = "homepage">
        <div class = "frontpage">
            <div class = "frontpage-content">
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
                $animals = [
                    ['name' => 'Lion', 'image' => '../assets/img/lion-animal.jpg', 'desc' => 'Plush for Guardian, Protector and Classroom levels:', 'image1' => '../assets/img/lion-plush.png'],
                    ['name' => 'Sea Turtle', 'image' => '../assets/img/seaturtle-animal.jpg', 'desc' => 'Plush for Guardian, Protector and Classroom levels:', 'image1' => '../assets/img/seaturtle-plush.png'],
                    ['name' => 'Chimpanzee', 'image' => '../assets/img/chimp-animal.jpg', 'desc' => 'Plush for Guardian, Protector and Classroom levels:', 'image1' => '../assets/img/chimpanzee-plush.avif'],
                ];
            ?>
            <?php foreach ($animals as $index => $animal): ?>
            <div class="animal-card">
                <input type="radio" name="animal-toggle" id="toggle-<?php echo $index; ?>">
                <label for="toggle-<?php echo $index; ?>">
                    <img class="responsive-circle" src="<?php echo $animal['image']; ?>" alt="<?php echo $animal['name']; ?>">
                    <span class = "animal-label"><?php echo $animal['name'];?></span>
                </label>
                <div class="dropdown-content">
                    <span class="close-btn" onclick="document.getElementById('toggle-<?php echo $index; ?>').checked = false;">×</span>
                    <p><?php echo $animal['desc']; ?></p>
                    <img class="plush-image" src="<?php echo $animal['image1']; ?>" alt="<?php echo $animal['name']; ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <?php
            if (isset($_GET['animals'])){
                $selectedAnimal = $_GET['animals'];
                $desc = '';
                $image1 = '';

                foreach($animals as $animal){
                    if ($animal['name'] === $selectedAnimal){
                        $desc = $animal['desc'];
                        $image1 = $animal['image1'];
                        break;
                    }
                }

                    if($desc): ?>
                        <div class = "dropdown-content">
                            <a href="adopt.php" class = "close-btn">x</a>
                            <div class = "dropdown-body">
                                <p><?php echo htmlspecialchars($desc); ?></p>
                                <img src = "<?php echo htmlspecialchars($image); ?>" class = "dropdown-img">
                            </div>
                        </div>
            <?php endif; } ?>
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