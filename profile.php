<?php
session_start();
include 'auth/conn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['studentId'])) {
    header("Location: login.php");
    exit();
}

$studentId = $_SESSION['studentId'];
$userType = $_SESSION['userType'];

// Fetch user details using studentId (since id is not stored in session)
$stmt = $conn->prepare("SELECT id, username, email, studentId, userType, course, subject FROM users WHERE studentId = ?");
$stmt->bind_param("s", $studentId);  // Use "s" because studentId is a string
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User data not found.");
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $course = $_POST['course'];
    $subject = $_POST['subject'];

    $update_stmt = $conn->prepare("UPDATE users SET username = ?, course = ?, subject = ? WHERE studentId = ?");
    $update_stmt->bind_param("ssss", $username, $course, $subject, $studentId);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php"); // Redirect to prevent form resubmission
        exit();
    } else {
        $_SESSION['error'] = "Error updating profile: " . $update_stmt->error;
    }
}

// Handle Password Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match!";
    } else {
        $pass_stmt = $conn->prepare("SELECT password FROM users WHERE studentId = ?");
        $pass_stmt->bind_param("s", $studentId);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $row = $pass_result->fetch_assoc();

        if ($row && password_verify($currentPassword, $row['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update_pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE studentId = ?");
            $update_pass_stmt->bind_param("ss", $hashedPassword, $studentId);

            if ($update_pass_stmt->execute()) {
                $_SESSION['success'] = "Password updated successfully!";
                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating password.";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect!";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<style>
    .profile-container {
        width: 80%;
        max-width: 800px;
        margin: auto;
        display: flex;
        gap: 20px;
        padding: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .profile-section,
    .password-section {
        flex: 1;
        min-width: 320px;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
    }

    h2 {
        text-align: center;
        margin-bottom: 15px;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: bold;
        margin-top: 10px;
    }

    input {
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .success {
        color: green;
        text-align: center;
    }

    .error {
        color: red;
        text-align: center;
    }

    /* Password reveal styling */
    .password-container {
        display: flex;
        align-items: center;
        position: relative;
    }

    .password-container input {
        flex: 1;
        padding-right: 30px;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        cursor: pointer;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .container {
            width: 95%;
            flex-direction: column;
            padding: 10px;
        }

        .profile-section,
        .password-section {
            width: 100%;
            padding: 15px;
        }

        input,
        button {
            font-size: 16px;
            padding: 12px;
        }

        h2 {
            font-size: 20px;
        }
    }
</style>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="website-name">UPTM Study Ease</span>
        </div>

        <!-- Desktop Navigation -->
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Inbox</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Mobile Menu -->
        <div class="hamburger">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu">
        <a href="home.php">Home</a>
        <a href="inbox.php">Inbox</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
<!-- Main Profile Page -->
<div class="profile-container">
    <!-- Left: Profile Update -->
    <div class="profile-section">
        <h2>Update Profile</h2>

        <?php if (isset($_SESSION['success'])) { echo "<p class='success'>{$_SESSION['success']}</p>"; unset($_SESSION['success']); } ?>
        <?php if (isset($_SESSION['error'])) { echo "<p class='error'>{$_SESSION['error']}</p>"; unset($_SESSION['error']); } ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" 
                value="<?= isset($user['username']) ? htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') : '' ?>" required>

            <label>Email (cannot be changed):</label>
            <input type="email" 
                value="<?= isset($user['email']) ? htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') : '' ?>" readonly>

            <label>ID (cannot be changed):</label>
            <input type="text" 
                value="<?= isset($user['studentId']) ? htmlspecialchars($user['studentId'], ENT_QUOTES, 'UTF-8') : '' ?>" readonly>

            <label>Course:</label>
            <input type="text" name="course"
                value="<?= isset($user['course']) ? htmlspecialchars($user['course'], ENT_QUOTES, 'UTF-8') : '' ?>" required>


            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>

    <!-- Right: Password Update -->
    <div class="password-section">
        <h2>Change Password</h2>

        <form method="POST">
            <label>Current Password:</label>
            <div class="password-container">
                <input type="password" name="current_password" id="current_password" required>
                <span class="toggle-password" onclick="togglePassword('current_password')">👁️</span>
            </div>

            <label>New Password:</label>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" required>
                <span class="toggle-password" onclick="togglePassword('new_password')">👁️</span>
            </div>

            <label>Confirm New Password:</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
            </div>

            <button type="submit" name="update_password">Update Password</button>
        </form>
    </div>
</div>



</body>
<script>
    function togglePassword(inputId) {
        var input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const hamburger = document.querySelector(".hamburger");
        const mobileMenu = document.querySelector(".mobile-menu");
        const navLinks = document.querySelector(".nav-links");

        hamburger.addEventListener("click", function() {
            mobileMenu.classList.toggle("active");
        });

        function checkScreenSize() {
            if (window.innerWidth > 768) {
                mobileMenu.classList.remove("active");
                navLinks.style.display = "flex";
            } else {
                navLinks.style.display = "none";
            }
        }
        checkScreenSize();
        window.addEventListener("resize", checkScreenSize);
    });
</script>

</html>