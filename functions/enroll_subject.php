<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$userId = $_SESSION['userId'];

$subjectIds = $_POST['subjects'] ?? []; // Updated to handle multiple subjects

// Validate subject_id
if (empty($subjectIds)) {
    echo json_encode(["status" => "error", "message" => "No subjects selected"]);
    exit;
}

foreach ($subjectIds as $index => $subjectId) {
    $subjectName = $_POST['subject_names'][$index]; // Retrieve the corresponding subject name
    if (!is_string($subjectId)) {
        echo json_encode(["status" => "error", "message" => "Invalid subject selection"]);
        exit;
    }

    // Check if the user is already enrolled in the subject
    $checkQuery = "SELECT userId, subject_id FROM enrollments WHERE userId = ? AND subject_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $userId, $subjectId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // User is already enrolled in this subject, skip to the next subject
        continue;
    }

    // Enroll the user
    $query = "INSERT INTO enrollments (userId, subject_id, subject_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $userId, $subjectId, $subjectName);

    if (!$stmt->execute()) {
        error_log("Database error: " . $stmt->error); // Log the error for debugging
        echo json_encode(["status" => "error", "message" => "Database error occurred."]);
        exit;
    }
}

header("Location: ../home.php"); // Redirect to home after successful enrollment
exit();

$stmt->close();
$conn->close();
?>
