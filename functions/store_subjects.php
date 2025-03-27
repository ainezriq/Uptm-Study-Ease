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

    // Check if each subject exists before inserting
    foreach ($subjects as $subject) {
        $checkQuery = "SELECT id FROM subjects WHERE subject_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $subject);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            echo json_encode(["error" => "Subject ID $subject does not exist."]);
            exit;
        }
        $checkStmt->close();
    }

    // Prepare the insert statement
    $sql = "INSERT INTO enrollments (userId, subject_id) VALUES (?, ?)"; // Ensure correct insertion





    $stmt = $conn->prepare($sql);

    foreach ($subjects as $subject) {
$stmt->bind_param("is", $userId, $subject); // Changed from studentId to userId



        if (!$stmt->execute()) {
            echo json_encode(["error" => "Failed to enroll in subject: " . $stmt->error]);
            exit;
        }
    }

header("Location: ../home.php"); // Redirect to home.php on success
exit;

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
