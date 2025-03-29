<?php
session_start();
include 'auth/conn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch user details
$stmt = $conn->prepare("SELECT userId, username, email, userType, course FROM users WHERE userId = ?");
if (!$stmt) {
    error_log("Database query error: " . $conn->error);
}

$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || empty($user)) {
    die("User data not found.");
}

// Fetch enrolled subjects
$subjects_stmt = $conn->prepare("SELECT subject_id FROM enrollments WHERE userId = ?"); 
$subjects_stmt->bind_param("s", $userId);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();
$enrolled_subjects = [];

while ($row = $subjects_result->fetch_assoc()) {
    $enrolled_subjects[] = $row['subject_id']; 
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['userId'])) {
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
    
    $delete_stmt = $conn->prepare("DELETE FROM enrollments WHERE userId = ?");
    if (!$delete_stmt) {
        error_log("Database query error: " . $conn->error);
    }

    $delete_stmt->bind_param("s", $userId);
    $delete_stmt->execute();

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
            <h2>Update Enrolled Subjects</h2>
            <form method="POST">
                <label>Currently Enrolled Subjects:</label>
                <select name="subjects[]" multiple>
                    <?php
                    $stmt = $conn->prepare("SELECT subject_id FROM subjects");
                    $stmt->execute();
                    $subject_result = $stmt->get_result();
                    $subject_id = [];
                    while ($row = $subject_result->fetch_assoc()) {
                        $subject_id[$row['subject_id']] = $row['subject_name'];
                    }

                    foreach ($subject_id as $id => $name) {
                        $selected = in_array($id, $enrolled_subjects) ? "selected" : "";
                        echo "<option value=\"$id\" $selected>$id - " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="update_subjects">Update Subjects</button>
            </form>
        </div>
        <?php endif; ?>
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
        <?php
        // Handle Password Update
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Fetch the current password from the database
            $password_stmt = $conn->prepare("SELECT password FROM users WHERE userId = ?");
            $password_stmt->bind_param("s", $userId);
            $password_stmt->execute();
            $password_result = $password_stmt->get_result();
            $user_data = $password_result->fetch_assoc();

            if ($user_data && password_verify($current_password, $user_data['password'])) {
                if ($new_password === $confirm_password) {
                    // Update the password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE userId = ?");
                    $update_password_stmt->bind_param("ss", $hashed_password, $userId);
                    if ($update_password_stmt->execute()) {
                        $_SESSION['success'] = "Password updated successfully!";
                    } else {
                        $_SESSION['error'] = "Error updating password";
                    }
                } else {
                    $_SESSION['error'] = "New passwords do not match.";
                }
            } else {
                $_SESSION['error'] = "Current password is wrong.";
            }
        }
        ?>
    </div>

</body>
</html>
