<?php

$conn = mysqli_connect("localhost:3307", "root", "", "products");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    session_unset();
    echo "success";
    exit();
}

$sql = "SELECT text_column, image_column, start_date, end_date FROM offer ORDER BY id DESC";
$result = $conn->query($sql);
$offers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
}

$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_results = [];

if (!empty($search_query)) {
    $search_query = trim($conn->real_escape_string($search_query));

    $sql = "
        (SELECT 'sofa' AS table_name, id, code, name, category, color, price, image, quantity FROM sofa WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'chairs', id, code, name, category, color, price, image, quantity FROM chairs WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'center_table', id, code, name, category, color, price, image, quantity FROM center_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'dining_table', id, code, name, category, color, price, image, quantity FROM dining_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'beds', id, code, name, category, color, price, image, quantity FROM beds WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'dressing_table', id, code, name, category, color, price, image, quantity FROM dressing_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'writing_table', id, code, name, category, color, price, image, quantity FROM writing_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'wardrobe', id, code, name, category, color, price, image, quantity FROM wardrobe WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'showcase', id, code, name, category, color, price, image, quantity FROM showcase WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC );
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}
$recent_products = [];
$recent_sql = "
    (SELECT 'sofa' AS table_name, id, code, name, category, color, price, image, quantity FROM sofa ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'chairs', id, code, name, category, color, price, image, quantity FROM chairs ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'center_table', id, code, name, category, color, price, image, quantity FROM center_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'dining_table', id, code, name, category, color, price, image, quantity FROM dining_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'beds', id, code, name, category, color, price, image, quantity FROM beds ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'dressing_table', id, code, name, category, color, price, image, quantity FROM dressing_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'writing_table', id, code, name, category, color, price, image, quantity FROM writing_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'wardrobe', id, code, name, category, color, price, image, quantity FROM wardrobe ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'showcase', id, code, name, category, color, price, image, quantity FROM showcase ORDER BY id DESC LIMIT 2)
    ORDER BY id DESC;
";

$recent_result = $conn->query($recent_sql);

if ($recent_result && $recent_result->num_rows > 0) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOOD FURNITURE</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">

</head>

<body>
    <header role="user">
        <div class="logo" onclick="reload()">WOOD FURNITURE</div>
        <nav>
            <a href="" id="a1">Products</a>
            <a href="" id="hist">History</a>
            <a href="" id="about">About Us</a>

            <div class="search-container">
                <form action="user_index.php" method="get" id="searchform">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search..."
                        class="search-box"
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        onkeydown="if(event.key === 'Enter'){this.form.submit();
                        }">
                </form>
            </div>
        </nav>
    </header>
    <a href="login.php" id="logout" onclick="logoutUser()" style="color:#17252a">Logout</a>

    <?php if (!empty($search_results)): ?>
        <section class="search-results-user">
            <h2>Search Results</h2>
            <div class="products" id="product-user">
                <?php foreach ($search_results as $product): ?>
                    <a href="user_products.php?category=<?php echo $product['table_name']; ?>&id=<?php echo $product['id']; ?>" class="product-category1">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <p>
                            <strong><?php echo $product['name']; ?></strong><br>
                            Price: ₹<?php echo $product['price']; ?><br>
                            Category: <?php echo $product['category']; ?>
                        </p>
                    </a>

                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif (!empty($search_query)): ?>
        <section class="search-results-user">
            <h2>No Results Found</h2>
            <p id="notfound">No products found for "<?php echo htmlspecialchars($search_query); ?>"</p>
        </section>
    <?php endif; ?>

    <div class="slider-container">
        <div class="slider">
            <?php foreach ($offers as $offer): ?>
                <div class="slide">
                    <div class="img-slide">
                        <img src="uploads/<?= htmlspecialchars($offer['image_column']) ?>" alt="Offer Image">
                    </div>
                    <div class="offer-details">
                        <p><?= htmlspecialchars($offer['text_column']) ?></p>
                        <small>Offer Valid: <?= htmlspecialchars($offer['start_date']) ?> - <?= htmlspecialchars($offer['end_date']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
        <button class="next" onclick="moveSlide(1)">&#10095;</button>
    </div>



    <section class="recent-products-user">
        <h2>Recently Added Products</h2>
        <div class="products-user">
            <?php foreach ($recent_products as $product): ?>
                <a href="user_products.php?category=<?php echo $product['table_name']; ?>" class="product-category1">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <p>
                        <strong><?php echo $product['name']; ?></strong><br><br>
                        Price: ₹<?php echo $product['price']; ?><br><br>
                        Category: <?php echo $product['category']; ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hero">
        <h1>High-Quality Wooden Furniture for Every Home</h1>
        <h3> 4.8/5 Customer Satisfaction – Your Comfort, Our Priority!</h3>
        <h3>Furnishing Homes for 20+ Years with Premium Wooden Furniture!</h3>
    </section>

    <section class="products" id="products">
        <h2>Furniture Categories</h2>
        <a href="user_products.php?category=sofa" class="product-category">
            <img src="index_images/sofa.jpg" alt="Sofa">
            Sofa
        </a>
        <a href="user_products.php?category=chairs" class="product-category">
            <img src="index_images/chair.jpg" alt="Chair">
            Chairs
        </a>
        <a href="user_products.php?category=center_table" class="product-category">
            <img src="index_images/center_table.jpg" alt="Center Table">
            Center Table
        </a>
        <a href="user_products.php?category=dining_table" class="product-category">
            <img src="index_images/dining _table.jpg" alt="Dining Table">
            Dining Table
        </a>
        <a href="user_products.php?category=beds" class="product-category">
            <img src="index_images/bed.jpg" alt="Bed">
            Beds
        </a>
        <a href="user_products.php?category=dressing_table" class="product-category">
            <img src="index_images/dressing_tables.jpg" alt="Dressing Table">
            Dressing Table
        </a>
        <a href="user_products.php?category=writing_table" class="product-category">
            <img src="index_images/writing table.jpg" alt="Writing Table">
            Writing Table
        </a>
        <a href="user_products.php?category=wardrobe" class="product-category">
            <img src="index_images/wardrobe.jpg" alt="Wardrobe">
            Wardrobe
        </a>
        <a href="user_products.php?category=showcase" class="product-category">
            <img src="index_images/showcase.jpg" alt="Showcase">
            Showcase
        </a>
    </section>

    <div class="space"></div>

    <div class="history">
        <div class="c">
            <div class="shop_images">
                <img src="index_images/bed.jpg" class="slide1 active" alt="Image 1">
                <img src="index_images/chair.jpg" class="slide1" alt="Image 2">
                <img src="index_images/sofa.jpg" class="slide1" alt="Image 3">
                <img src="index_images/dining _table.jpg" class="slide1" alt="Image 4">
            </div>
            <div class="shop_history">
                <h2>Shop History</h2>
                <p>Our shop started as a small idea and has grown significantly over the years. Initially, we began with a small store, focusing on delivering quality products and excellent customer service.

                    As time passed, we prioritized customer satisfaction by staying updated with the latest trends, using the best materials, and offering products at affordable prices. This dedication has helped us expand and earn the trust of many loyal customers.</p>
            </div>
        </div>
    </div>

    <footer>
        <h1>About Us</h1>
        <h2>20+ Years of Excellence in the Furniture Business</h2>

        <div class="footer-container">
            <div class="footer-column">
                <h3>🏬 Shop Address</h3>
                <p>No. 123, Main Road,</p>
                <p>Chennai - 600001, India</p>
                <p><strong>🕒 Timings:</strong> 9:00 AM - 9:00 PM</p>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-column">
                <h3>📞 Contact Us</h3>
                <p><strong>Phone:</strong> +91 98765 43210</p>
                <p><strong>WhatsApp:</strong> +91 98765 43211</p>
                <p><strong>Email:</strong> support@furnitureshop.com</p>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-column" id="pro">
                <h3>🛋️ Our Products</h3>
                <p>Sofas, Beds, Chairs, Dining Tables</p>
                <p>Wardrobes, Dressing Tables, Showcases</p>
                <p>Center Tables, Writing Tables, More...</p>
            </div>
        </div>

        <div class="social-media">
            <a href="https://www.facebook.com" class="social-icon" aria-label="Facebook">
                <img src="index_images/facebook.jpg" alt="Facebook">
            </a>
            <a href="https://www.instagram.com" class="social-icon" aria-label="Instagram">
                <img src="index_images/insta.jpg" alt="Instagram">
            </a>
            <a href="https://www.whatsapp.com" class="social-icon" aria-label="Whatsapp">
                <img src="index_images/whatsapp.jpg" alt="Whatsapp">
            </a>
            <a href="https://www.twitter.com" class="social-icon" aria-label="Twitter">
                <img src="index_images/x.jpg" alt="Twitter">
            </a>
        </div>
    </footer>

    <script>
        let index = 0;
        const slides = document.querySelectorAll('.img-slide');
        const totalSlides = slides.length;
        const slider = document.querySelector('.slider');

        function moveSlide(direction) {
            index += direction;
            if (index >= totalSlides) index = 0;
            if (index < 0) index = totalSlides - 1;
            updateSlider();
        }

        function updateSlider() {
            slider.style.transform = `translateX(-${index * 100}%)`;
        }

        function autoSlide() {
            moveSlide(1);
        }

        setInterval(autoSlide, 3000);

        let slide = document.querySelectorAll(".slide1");
        let index1 = 0;

        function changeSlide() {
            slide[index1].classList.remove("active");
            index1 = (index1 + 1) % slide.length;
            slide[index1].classList.add("active");
        }
        setInterval(changeSlide, 3000);

        document.querySelector('#a1').addEventListener('click', function(event) {
            event.preventDefault();

            document.querySelector('#products').scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.querySelector('#pro').addEventListener('click', function(event) {
            event.preventDefault();

            document.querySelector('#products').scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.querySelector('#about').addEventListener('click', function(event) {
            event.preventDefault();

            document.querySelector('footer').scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.querySelector('#hist').addEventListener('click', function(event) {
            event.preventDefault();

            document.querySelector('.space').scrollIntoView({
                behavior: 'smooth'
            });
        });

        function logoutUser() {
            let confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                fetch('user_index.php?logout=true')
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            window.location.replace('login.php');
                        }
                    })
                    .catch(error => console.error('Logout failed:', error));
            }
        }

        function reload() {
            window.location.reload();
        }
        window.history.pushState(null, "", window.location.href);
        window.addEventListener("popstate", function() {
            window.location.replace("login.php");
        });
    </script>

</body>

</html>