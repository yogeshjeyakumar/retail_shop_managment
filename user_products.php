<?php
$conn = mysqli_connect("localhost:3307", "root", "", "products");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$category = isset($_GET['category']) ? $_GET['category'] : 'sofa'; 

$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$show = "SELECT * FROM $category";
if (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);

    if (is_numeric($search_query)) {
        $show .= " WHERE code = '$search_query'"; 
    } else {
        $show .= " WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%'"; 
    }
}
$swrite = mysqli_query($conn, $show);

echo "<div class='products'>";
if (mysqli_num_rows($swrite) > 0) {
    while ($select = mysqli_fetch_assoc($swrite)) {
        echo "<div class='product'>";
        $image_url = $select['image'];
        echo "<img src='" . $image_url . "' alt='Product Image' class='img'>";
        echo "<table class='items-table'>";
        echo "<tr><td>Product Name</td><td>:</td><td>" . $select['name'] . "</td></tr>";
        echo "<tr><td>Product Color</td><td>:</td><td>" . $select['color'] . "</td></tr>";
        echo "<tr><td>MRP</td><td>: ₹</td><td> <s>" . $select['price'] + 1500 . "</s> </td></tr>";
        echo "<tr><td>Product Price</td><td>: ₹</td><td>" . $select['price'] . "</td></tr>";
        echo "</table>";
        echo "</div>";
    }
} else {
    echo "<center><p>No products found for '$search_query'.</center></p>";
}
echo "</div>";

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="css/products.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="logo">WOOD FURNITURE</div>
        <nav>
            <a href="user_index.php">Home</a>
            <a href="" id="hist">History</a>
            <a href="" id="about">About Us</a>

            <div class="search-container">
                <form action="products.php?category=<?php echo $category; ?>" method="get" id="searchform">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search..."
                        class="search-box"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        onkeydown="if(event.key === 'Enter'){this.form.submit();}">
                </form>
            </div>

        </nav>
    </header>

    <div class="space"></div>
    <div class="history">
        <div class="c">
            <div class="shop_images">
                <img src="index_images/bed.jpg" class="slide active" alt="Image 1">
                <img src="index_images/chair.jpg" class="slide" alt="Image 2">
                <img src="index_images/sofa.jpg" class="slide" alt="Image 3">
                <img src="index_images/dining _table.jpg" class="slide" alt="Image 4">
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

            <div class="footer-column">
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

</body>
<script>
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

    let slides = document.querySelectorAll(".slide");
    let index = 0;

    function changeSlide() {
        slides[index].classList.remove("active");
        index = (index + 1) % slides.length;
        slides[index].classList.add("active");
    }
    setInterval(changeSlide, 3000);
</script>

</html>