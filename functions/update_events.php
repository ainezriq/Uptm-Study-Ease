<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (!isset($_SESSION['userId'])) { // Changed from studentId to userId

        echo "Error: User not logged in";
        exit;
    }

    $id = $_POST['id'] ?? null;
    $new_date = $_POST['new_date'] ?? null;
$userId = $_SESSION['userId']; // Changed from studentId to userId


    if (!$id || !$new_date) {
        echo "Error: Missing event ID or new date";
        exit;
    }

$stmt = $conn->prepare("UPDATE user_events SET event_date = ? WHERE id = ? AND userId = ?"); // Updated query

    $stmt->bind_param("sis", $new_date, $id, $studentId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error: No changes made or event not found";
    }

    $stmt->close();
    $conn->close();
}
