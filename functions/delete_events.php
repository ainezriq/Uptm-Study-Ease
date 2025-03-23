<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['studentId'])) {
        echo "Error: User not logged in";
        exit;
    }

    $id = $_POST['id'] ?? null;
    $studentId = $_SESSION['studentId'];

    if (!$id) {
        echo "Error: Missing event ID";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM user_events WHERE id = ? AND studentId = ?");
    $stmt->bind_param("is", $id, $studentId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error: Event not found or could not be deleted";
    }

    $stmt->close();
    $conn->close();
}
