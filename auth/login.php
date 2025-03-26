<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$password = $_POST['password']; // Keep password handling as plain text

    
    if (isset($_POST['register'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $email = trim(strtolower($_POST['email']));
        $userId = trim($_POST['userId']);
        $course = trim($_POST['course']);
        $userType = trim($_POST['userType']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../index.php?error=invalid_email");
            exit();
        }

        // Check if user ID exists
        $stmt = $conn->prepare("SELECT userId FROM users WHERE userId = ?"); // Updated query
        $stmt->bind_param("s", $userId);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            header("Location: ../index.php?error=userId_exists"); // Updated error message
            exit();
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO users (username, email, userId, password, userType, course) VALUES (?, ?, ?, ?, ?, ?)"); // Updated query
        $stmt->bind_param("ssssss", $username, $email, $userId, $password, $userType, $course);

        if ($stmt->execute()) {
            header("Location: ../index.php?success=registered");
        } else {
            header("Location: ../index.php?error=registration_failed");
        }
        $stmt->close();
    } elseif (isset($_POST['login'])) {
        $userId = trim($_POST['userId']); // Changed from studentId to userId
        echo "Debug: User ID entered: " . $userId; // Debugging statement
        
$stmt = $conn->prepare("SELECT password, userType, course FROM users WHERE userId = ?"); // Updated query

        $stmt->bind_param("s", $userId);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_password, $userType, $course);
            $stmt->fetch();
            echo "Debug: Password entered: " . $password; // Debugging statement
if ($password === $db_password) { // Direct password check

                $_SESSION['userId'] = $userId; // Updated session variable
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
