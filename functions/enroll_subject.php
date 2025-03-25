<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) { // Changed from studentId to userId

    echo json_encode(["status" => "error", "message" => "Student not authenticated"]);
    exit;
}

$userId = $_SESSION['userId']; // Changed from studentId to userId

$subjectId = $_POST['subject_id'] ?? null;

// Validate subject_id
if (!$subjectId || !is_numeric($subjectId)) {
    echo json_encode(["status" => "error", "message" => "Invalid subject selection"]);
    exit;
}

// Get user ID from studentId
$query = "SELECT id FROM users WHERE userId = ?"; // Updated query

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$userId = $user['id'];

// Check if the subject exists
$query = "SELECT id FROM subjects WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $subjectId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Subject not found"]);
    exit;
}

// Check if the student is already enrolled
$query = "SELECT id FROM enrollments WHERE user_id = ? AND subject_id = ?"; // Updated query

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $subjectId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Already enrolled in this subject"]);
    exit;
}

error_log("Attempting to enroll student: $userId in subject: $subjectId");
// Enroll the student

$query = "INSERT INTO enrollments (user_id, subject_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $subjectId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Enrollment successful"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);

}

$stmt->close();
$conn->close();
?>
