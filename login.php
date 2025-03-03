<?php
session_start();
$conn = new mysqli("localhost:3307", "root", "", "products");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? "index.php" : "user_index.php"));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    $query = "SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $identifier, $identifier, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . ($user['role'] == 'admin' ? "index.php" : "user_index.php"));
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $security_answer = trim($_POST['security_answer']);
    $role = "user";
    $check_query = "SELECT id FROM users WHERE phone = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $phone);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "User already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, phone, password, role, security_answer) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $phone, $hashed_password, $role, $security_answer);
        mysqli_stmt_execute($stmt);
        $success = "Account created successfully! Please login.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    $identifier = trim($_POST['identifier']);

    $query = "SELECT security_question, security_answer FROM users WHERE email = ? OR phone = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['identifier'] = $identifier;
        $_SESSION['security_question'] = $user['security_question'];
        $_SESSION['security_answer_db'] = $user['security_answer'];
        $forgot_stage = "verify_answer";
    } else {
        $error = "User not found!";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $entered_answer = trim($_POST['security_answer']);
    $new_password = trim($_POST['new_password']);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $identifier = $_SESSION['identifier'];
    if ($entered_answer === $_SESSION['security_answer_db']) {
        $update_query = "UPDATE users SET password = ? WHERE email = ? OR phone = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $hashed_password, $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        session_destroy();
        header("Location: login.php?success=Password Updated Successfully");
        exit();
    } else {
        $error = "Incorrect security answer!";
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login & Sign Up</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
</head>

<body>
    <div class="login-container">


        <?php if (isset($error)) {
            echo "<p style='color: red;'>$error</p>";
        } ?>
        <?php if (isset($success)) {
            echo "<p style='color: green;'>$success</p>";
        } ?>
        <div id="login-section" <?php if (isset($forgot_stage)) echo 'style="display: none;"'; ?>>
            <form method="POST">
                <h2>Welcome Back</h2>
                <h4>Sign Up</h4>
                <label>Email or Phone:</label>
                <input type="text" name="identifier" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <div class="role-selection">
                    <button id="user-btn" class="role-btn active" onclick="selectRole('user', event)">User</button>
                    <button id="admin-btn" class="role-btn" onclick="selectRole('admin', event)">Admin</button>
                </div>

                <input type="hidden" name="role" id="role" value="user">
                <button type="submit" name="login">Login</button>
            </form>
            <p><a href="#" onclick="showSection('signup'); return false;">New User? Sign Up</a></p>
            <p><a href="#" onclick="toggleForgotPassword(); return false;">Forgot Password?</a></p>
        </div>

        <div id="signup-section" style="display: none;">
            <form method="POST">
                <h2>Login</h2>
                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Phone:</label>
                <input type="text" name="phone" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label style="font-size: 13px">Favourite Name(for forgot password):</label>
                <input type="text" name="security_answer" required>

                <button type="submit" name="signup">Sign Up</button>
            </form>
            <p><a href="#" onclick="showSection('login'); return false;">Already have an account? Login</a></p>
        </div>

        <div id="forgot-section" style="display: none;">
            <form method="POST">
                <label>Enter Email or Phone:</label>
                <input type="text" name="identifier" required>
                <button type="submit" name="forgot_password">Next</button>
            </form>
            <p><a href="#" onclick="toggleForgotPassword(); return false;">Back to Login</a></p>
        </div>

        <?php if (isset($forgot_stage) && $forgot_stage == "verify_answer") { ?>
            <div class="forgot-section">
                <h3>Security Question</h3>
                <form method="POST">
                    <label><?php echo $_SESSION['security_question']; ?></label>
                    <input type="text" name="security_answer" required>

                    <label>New Password:</label>
                    <input type="password" name="new_password" required>

                    <button type="submit" name="reset_password">Reset Password</button>
                </form>
            </div>
        <?php } ?>
    </div>

    <img src="index_images/sofa roll.png" id="sofa_anim">

    <img src="index_images/sofa roll2.png" id="sofa_anim2">


</body>

</html>

<script>
    function showSection(section) {
        document.getElementById('login-section').style.display = section === 'login' ? 'block' : 'none';
        document.getElementById('signup-section').style.display = section === 'signup' ? 'block' : 'none';
        document.getElementById('forgot-section').style.display = section === 'forgot' ? 'block' : 'none';
    }

    function toggleForgotPassword() {
        let forgotSection = document.getElementById('forgot-section');
        let loginSection = document.getElementById('login-section');

        if (forgotSection.style.display === "none") {
            forgotSection.style.display = "block";
            loginSection.style.display = "none";
        } else {
            forgotSection.style.display = "none";
            loginSection.style.display = "block";
        }
    }

    function selectRole(role, event) {
        event.preventDefault();
        document.getElementById("role").value = role;
        document.getElementById("user-btn").classList.remove("active");
        document.getElementById("admin-btn").classList.remove("active");
        document.getElementById(role + "-btn").classList.add("active");
    }

    window.onpopstate = function(event) {
        showSection('login');
    };
</script>