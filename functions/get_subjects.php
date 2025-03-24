<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['studentId'])) {
    echo json_encode(["status" => "error", "message" => "Student not authenticated"]);
    exit;
}

$studentId = $_SESSION['studentId'];
$subjects = [];

// Get user ID from studentId
$query = "SELECT id FROM users WHERE studentId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$stmt->close();

// Fetch subjects assigned to the logged-in student
$query = "SELECT s.subject_code, s.subject_name 
          FROM subjects s
          JOIN enrollments e ON s.id = e.subject_id
          WHERE e.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = ["code" => $row['subject_code'], "name" => $row['subject_name']];
    }
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "subjects" => $subjects]);
?>
