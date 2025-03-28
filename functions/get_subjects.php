<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) { // Changed from studentId to userId
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$userId = $_SESSION['userId']; // Changed from studentId to userId

$subjects = [];

// Get user ID from userId
$query = "SELECT id FROM users WHERE userId = ?"; // Updated query to match your database schema


$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userId); // Changed from studentId to userId
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$stmt->close();

// Fetch subjects assigned to the logged-in user
$query = "SELECT s.subject_code, s.subject_name 
          FROM subjects s
          JOIN enrollments e ON s.id = e.subject_id
          WHERE e.userId = ?"; // Updated to match the correct column name


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

    echo json_encode(["status" => "success", "subjects" => $subjects, "message" => "Subjects fetched successfully."]);

?>
