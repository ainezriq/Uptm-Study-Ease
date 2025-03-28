<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['userId'])) {
        echo "Error: User not logged in";
        exit;
    }

    $eventId = $_POST['id'] ?? null;
    $userId = $_SESSION['userId'];

    if (!$eventId) {
        echo "Error: Missing event ID";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM user_events WHERE eventId = ? AND userId = ?");
    $stmt->bind_param("is", $eventId, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error: Event not found or could not be deleted";
    }

    $stmt->close();
    $conn->close();
}
