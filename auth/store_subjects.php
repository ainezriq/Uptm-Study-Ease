<?php
session_start();
include '../auth/conn.php'; // Ensure this file connects to your database

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['subjects'])) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, email) VALUES (?, ?)");
        foreach ($_POST['subjects'] as $subject) {
            $subject = trim($subject);
            if (!empty($subject)) {
                $stmt->bind_param("ss", $subject, $email);
                $stmt->execute();
            }
        }
        echo json_encode(["success" => "Subjects saved successfully!"]);
    } else {
        echo json_encode(["error" => "No subjects selected."]);
    }
    exit;
}
?>
