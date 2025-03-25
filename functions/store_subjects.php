<?php
session_start();
include '../auth/conn.php'; // Include database connection

if (!isset($_SESSION['userId'])) { // Changed from studentId to userId

    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$userId = $_SESSION['userId']; // Changed from studentId to userId

    $subjects = $_POST['subjects'] ?? [];

    if (empty($subjects)) {
        echo json_encode(["error" => "No subjects selected"]);
        exit;
    }

    // Prepare the insert statement
    $sql = "INSERT INTO enrollments (user_id, subject_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($subjects as $subject) {
$stmt->bind_param("ss", $userId, $subject); // Changed from studentId to userId

        if (!$stmt->execute()) {
            echo json_encode(["error" => "Failed to enroll in subject: " . $stmt->error]);
            exit;
        }
    }

    echo json_encode(["success" => "Successfully enrolled in selected subjects"]);
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
