<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['studentId'])) {
    echo json_encode(["status" => "error", "message" => "Student not authenticated"]);
    exit;
}

$studentId = $_SESSION['studentId'];
$subjectId = $_POST['subject_id'] ?? null;

if (!$subjectId) {
    echo json_encode(["status" => "error", "message" => "Invalid subject selection"]);
    exit;
}

// Get user ID from studentId
$query = "SELECT id FROM users WHERE studentId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userId = $user['id'];
$stmt->close();

// Check if the student is already enrolled
$query = "SELECT * FROM enrollments WHERE user_id = ? AND subject_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $subjectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Already enrolled in this subject"]);
    exit;
}
$stmt->close();

// Enroll the student
$query = "INSERT INTO enrollments (user_id, subject_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $subjectId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Enrollment successful"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>
