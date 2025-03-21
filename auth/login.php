<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    
    if (isset($_POST['register'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $email = trim(strtolower($_POST['email']));
        $studentId = trim($_POST['studentId']); // Student ID for login
        $course = trim($_POST['course']);
        $userType = trim($_POST['userType']); // User selects Student or Lecturer
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../index.php?error=invalid_email");
            exit();
        }

        // Check if student ID exists
        $stmt = $conn->prepare("SELECT studentId FROM users WHERE studentId = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            header("Location: ../index.php?error=studentId_exists");
            exit();
        }
        $stmt->close();

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, studentId, password, userType, course) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $studentId, $hashedPassword, $userType, $course);

        if ($stmt->execute()) {
            header("Location: ../index.php?success=registered");
        } else {
            header("Location: ../index.php?error=registration_failed");
        }
        $stmt->close();
    } elseif (isset($_POST['login'])) {
        $studentId = trim($_POST['studentId']);
        
        $stmt = $conn->prepare("SELECT password, userType, course FROM users WHERE studentId = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_password, $userType, $course);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
                $_SESSION['studentId'] = $studentId;
                $_SESSION['userType'] = $userType;
                $_SESSION['course'] = $course;
                header("Location: ../home.php");
                exit();
            } else {
                header("Location: ../index.php?error=invalid_password");
            }
        } else {
            header("Location: ../index.php?error=user_not_found");
        }
        $stmt->close();
    }
    $conn->close();
}
