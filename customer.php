<?php

$conn = new mysqli("localhost:3307", "root", "", "products");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM offer WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: customer.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'], $_POST['new_text'], $_POST['new_start_date'], $_POST['new_end_date'])) {
    $edit_id = $_POST['edit_id'];
    $new_text = $_POST['new_text'];
    $new_start_date = $_POST['new_start_date'];
    $new_end_date = $_POST['new_end_date'];

    if (strtotime($new_start_date) >= strtotime($new_end_date)) {
        echo '<script>alert("Start date should be before the end date!")</script>';
    } else {
        $stmt = $conn->prepare("UPDATE offer SET text_column = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $new_text, $new_start_date, $new_end_date, $edit_id);
        if ($stmt->execute()) {
            header("Location: customer.php");
            exit();
        }
    }
}

$conn->query("DELETE FROM offer WHERE end_date < CURDATE()");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['text_column'])) {
    $text = $_POST['text_column'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (strtotime($start_date) >= strtotime($end_date)) {
        echo '<script>alert("Start date should be before the end date!")</script>';
    } else {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $imageFileName = basename($_FILES["image_column"]["name"]);
        $targetFilePath = $targetDir . $imageFileName;

        if (move_uploaded_file($_FILES["image_column"]["tmp_name"], $targetFilePath)) {
            $stmt = $conn->prepare("INSERT INTO offer (text_column, image_column, start_date, end_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $text, $imageFileName, $start_date, $end_date);
            if ($stmt->execute()) {
                header("Location: customer.php");
                exit();
            }
        }
    }
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$customer_query = "SELECT 
    customer.name AS customer_name,
    customer.phone AS customer_phone,
    customer.address AS customer_address,
    customer.payment_option AS payment_option,
    customer.total AS total_amount,
    customer.generated_at AS generated_at,
    customer.products_names AS products_names,
    (SELECT COUNT(*) FROM customer AS c WHERE c.phone = customer.phone) AS purchase_count
FROM customer";

if (!empty($search_query)) {
    $customer_query .= " WHERE customer.name LIKE ? OR customer.phone LIKE ? OR customer.products_names LIKE ?";
}

$customer_query .= " ORDER BY customer.generated_at DESC";

$stmt = $conn->prepare($customer_query);

if (!empty($search_query)) {
    $search_param = "%$search_query%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$customer_result = $stmt->get_result();
$customer_details = $customer_result->fetch_all(MYSQLI_ASSOC);
$user_query = "SELECT COUNT(*) AS total_users FROM users";
$user_result = $conn->query($user_query);
$total_users = ($user_result->fetch_assoc()['total_users'] ?? 0) - 1;
$users_query = "SELECT name, phone FROM users";
$users_result = $conn->query($users_query);
$users_list = $users_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks - WOOD FURNITURE</title>
    <link rel="stylesheet" href="css/stocks.css">
    <link rel="stylesheet" href="css/offer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
</head>

<body>

    <header>
        <div class="logo">WOOD FURNITURE</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="billing.php">Billing</a>
            <a href="stocks.php">Stock</a>
            <a href="" id="login_user">Users</a>
            <a href="" id="offer">Offer</a>

            <form action="customer.php" method="get" id="searchform">
                <input
                    type="text"
                    name="search"
                    placeholder="Search..."
                    class="search-box"
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    onkeydown="if(event.key === 'Enter'){this.form.submit();}">
            </form>

        </nav>
    </header>

    <section class="customer-details" id="customer-details">
        <h2>Customer Purchase Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Payment Option</th>
                    <th>Total Amount</th>
                    <th>Generated At</th>
                    <th>Products Purchased</th>
                    <th>Customer Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customer_details) > 0): ?>
                    <?php foreach ($customer_details as $customer): ?>
                        <tr>
                            <td><?php echo $customer['customer_name']; ?></td>
                            <td><?php echo $customer['customer_phone']; ?></td>
                            <td><?php echo $customer['customer_address']; ?></td>
                            <td><?php echo $customer['payment_option']; ?></td>
                            <td>₹<?php echo $customer['total_amount']; ?></td>
                            <td><?php echo $customer['generated_at']; ?></td>
                            <td><?php echo $customer['products_names']; ?></td>
                            <td>
                                <?php

                                if ($customer['purchase_count'] == 1) {
                                    echo "New";
                                } else {
                                    echo "Regular";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No matching results found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>


    <?php
    $conn = new mysqli("localhost:3307", "root", "", "products");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) AS total_users FROM users";
    $result = $conn->query($sql);

    $total_users = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_users = $row['total_users'] - 1;
    }


    ?>
    <div class="card1"></div>
    <div class="card">

        <div class="number" id="userCount">0</div>
        <div class="text">Number of Users Login Our Page</div>
    </div>

    <?php
    $conn = new mysqli("localhost:3307", "root", "", "products");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT name, phone FROM users";
    $result = $conn->query($sql);

    $users_list = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users_list[] = $row;
        }
    }

    ?>

    <section class="users-list">
        <h2>Registered Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users_list) > 0): ?>
                    <?php foreach ($users_list as $user): ?>
                        <tr>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['phone']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <script>
        function validateForm() {
            var startDate = document.getElementById("start_date").value;
            var endDate = document.getElementById("end_date").value;

            if (new Date(startDate) >= new Date(endDate)) {
                alert("Start date should be before the end date!");
                return false;
            }
            return true;
        }

        function removeOffer(id) {
            if (confirm("Are you sure you want to delete this offer?")) {
                var formData = new FormData();
                formData.append("delete_id", id);

                fetch("customer.php", {
                        method: "POST",
                        body: formData
                    }).then(response => response.text())
                    .then(() => location.reload());
            }
        }

        function editOffer(id, currentText, currentStartDate, currentEndDate) {
            var newText = prompt("Enter new text:", currentText);
            var newStartDate = prompt("Enter new start date (YYYY-MM-DD):", currentStartDate);
            var newEndDate = prompt("Enter new end date (YYYY-MM-DD):", currentEndDate);

            if (newText && newStartDate && newEndDate) {
                if (new Date(newStartDate) >= new Date(newEndDate)) {
                    alert("Start date should be before the end date!");
                    return;
                }

                var formData = new FormData();
                formData.append("edit_id", id);
                formData.append("new_text", newText);
                formData.append("new_start_date", newStartDate);
                formData.append("new_end_date", newEndDate);

                fetch("customer.php", {
                        method: "POST",
                        body: formData
                    }).then(response => response.text())
                    .then(() => location.reload());
            }
        }
    </script>

    <div class="space"></div>
    <div class="container">
        <h2>Upload Offer</h2>
        <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="text" name="text_column" placeholder="Enter text" required>
            <input type="file" name="image_column" accept="image/*" required>
            <input type="date" name="start_date" id="start_date" required>
            <input type="date" name="end_date" id="end_date" required>
            <button type="submit">Upload</button>
        </form>

        <h3>Offers</h3>
        <table>
            <tr>
                <thead>
                    <th>Text</th>
                    <th>Image</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </thead>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM offer");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['text_column']}</td>
                    <td><img src='uploads/{$row['image_column']}' width='50'></td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>
                        <button class='edit-btn' onclick='editOffer({$row['id']}, \"{$row['text_column']}\", \"{$row['start_date']}\", \"{$row['end_date']}\")'>Edit</button>
                        <button class='remove-btn' onclick='removeOffer({$row['id']})'>Remove</button>
                    </td>
                  </tr>";
            }
            ?>
        </table>
    </div>

    <footer>
        <p>&copy; 2025 WOOD FURNITURE. All Rights Reserved.</p>
    </footer>

</body>

<script>
    function animateCount(target, element) {
        let start = 0;
        let end = target;
        let duration = 500;
        let stepTime = Math.abs(Math.floor(duration / end));
        let timer = setInterval(() => {
            start++;
            element.textContent = start;
            if (start >= end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    let totalUsers = <?php echo $total_users; ?>;
    let countElement = document.getElementById("userCount");
    animateCount(totalUsers, countElement);



    document.querySelector('#login_user').addEventListener('click', function(event) {
        event.preventDefault();

        document.querySelector('.card1').scrollIntoView({
            behavior: 'smooth'
        });
    });

    document.querySelector('#offer').addEventListener('click', function(event) {
        event.preventDefault();

        document.querySelector('.space').scrollIntoView({
            behavior: 'smooth'
        });
    });
</script>

</html>