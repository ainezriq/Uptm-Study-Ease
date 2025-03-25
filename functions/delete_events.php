<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (!isset($_SESSION['userId'])) { // Changed from studentId to userId

        echo "Error: User not logged in";
        exit;
    }

    $id = $_POST['id'] ?? null;
$userId = $_SESSION['userId']; // Changed from studentId to userId


    if (!$id) {
        echo "Error: Missing event ID";
        exit;
    }

$stmt = $conn->prepare("DELETE FROM user_events WHERE id = ? AND userId = ?"); // Updated query

    $stmt->bind_param("is", $id, $studentId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error: Event not found or could not be deleted";
    }

    $stmt->close();
    $conn->close();
}
