<?php
session_start();
include '../auth/conn.php'; // Include database connection

if (!isset($_SESSION['userId'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId'];
    $subjects = $_POST['subjects'] ?? [];

    if (empty($subjects)) {
        echo json_encode(["error" => "No subjects selected. Please select at least one subject."]);
        exit;
    }

    // Check if each subject exists before inserting
    foreach ($subjects as $subject) {
        $checkQuery = "SELECT id FROM subjects WHERE id = ?"; // Updated to match the correct column name
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $subject);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            echo json_encode(["error" => "The subject ID $subject does not exist. Please check your selection."]);
            exit;
        }
        $checkStmt->close();
    }

    // Prepare the insert statement
    $sql = "INSERT INTO enrollments (userId, subject_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($subjects as $subject) {
        $stmt->bind_param("is", $userId, $subject);

        if (!$stmt->execute()) {
            echo json_encode(["error" => "Failed to enroll in subject: " . $stmt->error]);
            exit;
        }
    }

    echo json_encode(["status" => "success", "message" => "Enrollment successful. You will now receive notices for the selected subjects."]);
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
