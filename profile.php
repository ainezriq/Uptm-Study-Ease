<?php
session_start();
include 'auth/conn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch user details
$stmt = $conn->prepare("SELECT userId, username, email, userType, course FROM users WHERE userId = ?");


$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User data not found.");
}

// Fetch enrolled subjects
$subjects_stmt = $conn->prepare("SELECT subject_id FROM enrollments WHERE userId = ?");

$subjects_stmt->bind_param("s", $userId);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();
$enrolled_subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $enrolled_subjects[] = $row['subject_code'];
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    
    $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE userId = ?");
    $update_stmt->bind_param("ss", $username, $userId);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating profile: " . $update_stmt->error;
    }
}

// Handle Subject Enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_subjects'])) {
    $selected_subjects = $_POST['subjects'] ?? [];
    
    $conn->query("DELETE FROM enrollments WHERE user_id = '$userId'");

    foreach ($selected_subjects as $subject_code) {
$insert_stmt = $conn->prepare("INSERT INTO enrollments (userId, subject_id) VALUES (?, ?)");

        $insert_stmt->bind_param("ss", $userId, $subject_code);
        if (!$insert_stmt->execute()) {
            error_log("Error inserting subject: " . $insert_stmt->error);
        }
    }
    
    $_SESSION['success'] = "Subjects updated successfully!";
    header("Location: profile.php");
    exit();
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
            width: 75%;
            flex-direction: column;
            padding: 10px;
        }

        .profile-section,
        .password-section {
            width: 70%;
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
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="hamburger">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu">
        <a href="home.php">Home</a>
        <a href="inbox.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="profile-container">
        <div class="profile-section">
            <h2>Update Profile</h2>
            <form method="POST">
                <label>Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required>

                <label>Email (cannot be changed):</label>
                <input type="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" readonly>

                <label>ID (cannot be changed):</label>
                <input type="text" value="<?= htmlspecialchars($user['userId'], ENT_QUOTES, 'UTF-8') ?>" readonly>

                <label>Course (cannot be changed):</label>
                <input type="text" value="<?= htmlspecialchars($user['course'], ENT_QUOTES, 'UTF-8') ?>" readonly>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>

        <?php if ($user['userType'] == 'Student'): ?>
        <div class="profile-section">
            <h2>Enroll in Subjects</h2>
            <form method="POST">
                <label>Subjects:</label>
                <select name="subjects[]" multiple>

                    <?php
                    $stmt = $conn->prepare("SELECT subject_id, subject_name FROM subjects");
                    $stmt->execute();
                    $subject_result = $stmt->get_result();
                    $subject_codes = [];
                    while ($row = $subject_result->fetch_assoc()) {
                        $subject_codes[$row['subject_id']] = $row['subject_name'];
                    }

                    foreach ($subject_codes as $subject_code) {
                        $subject_name = $subject_codes[$subject_code] ?? 'Unknown Subject';

                        $stmt->execute();
                        $subject_result = $stmt->get_result();
                        $subject = $subject_result->fetch_assoc();
                        $selected = in_array($subject_code, $enrolled_subjects) ? "selected" : "";
                        echo "<option value=\"$subject_code\" $selected>$subject_code - " . htmlspecialchars($subject_name, ENT_QUOTES, 'UTF-8') . "</option>";

                    }
                    ?>
                </select>
                <button type="submit" name="update_subjects">Update Subjects</button>
        </div>
        <?php endif; ?>

            </form>
        </div>
        <div class="password-section">
            <h2>Change Password</h2>
            <form method="POST">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
                <label>New Password:</label>
                <input type="password" name="new_password" required>
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" name="update_password">Update Password</button>
            </form>
        </div>

    </div>
</body>
</html>
