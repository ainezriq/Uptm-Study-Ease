<?php
session_start();
include 'auth/conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$userType = $_SESSION['userType'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, email, nric, studentId, userNo, userType, course, semester FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $nric = $_POST['nric'];
    $studentId = $_POST['studentId'];
    $userNo = $_POST['userNo'];
    $course = $_POST['course'];

    // Only get semester if it's available in $_POST
    $semester = isset($_POST['semester']) ? $_POST['semester'] : NULL;

    if ($semester !== NULL) {
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, nric = ?, studentId = ?, userNo = ?, course = ?, semester = ? WHERE email = ?");
        $update_stmt->bind_param("sssssss", $username, $nric, $studentId, $userNo, $course, $semester, $email);
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, nric = ?, studentId = ?, userNo = ?, course = ? WHERE email = ?");
        $update_stmt->bind_param("ssssss", $username, $nric, $studentId, $userNo, $course, $email);
    }

    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
        header("Refresh:0");
    } else {
        $error = "Error updating profile: " . $update_stmt->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $pass_stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $pass_stmt->bind_param("s", $email);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $row = $pass_result->fetch_assoc();

        if ($row && $currentPassword === $row['password']) {
            $update_pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_pass_stmt->bind_param("ss", $newPassword, $email);

            if ($update_pass_stmt->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Error updating password.";
            }
        } else {
            $error = "Current password is incorrect!";
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

            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <label>Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label>Email (cannot be changed):</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

                <label>NRIC:</label>
                <input type="text" name="nric" value="<?= htmlspecialchars($user['nric']) ?>" required>

                <?php if ($userType == 'Student') { ?>
                    <label>Student ID:</label>
                    <input type="text" name="studentId" value="<?= htmlspecialchars($user['studentId']) ?>" required>
                <?php } else { ?>
                    <label>Staff ID:</label>
                    <input type="text" name="studentId" value="<?= htmlspecialchars($user['studentId']) ?>" required>
                <?php } ?>

                <label>User No:</label>
                <input type="text" name="userNo" value="<?= htmlspecialchars($user['userNo']) ?>" required>

                <label>Course:</label>
                <input type="text" name="course" value="<?= htmlspecialchars($user['course']) ?>" disabled>

                <?php if ($userType == 'Student') { ?>
                    <label>Semester:</label>
                    <input type="text" name="semester" value="<?= htmlspecialchars($user['semester']) ?>" disabled>
                <?php } ?>

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