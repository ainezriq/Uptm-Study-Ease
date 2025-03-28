<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['userId'])) {
        echo "Error: User not logged in";
        exit;
    }

    $eventId = $_POST['id'] ?? null;
    $new_date = $_POST['new_date'] ?? null;
    $userId = $_SESSION['userId'];

    if (!$eventId || !$new_date) {
        echo "Error: Missing event ID or new date";
        exit;
    }

    $stmt = $conn->prepare("UPDATE user_events SET event_date = ? WHERE eventId = ? AND userId = ?");
    $stmt->bind_param("sis", $new_date, $eventId, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error: No changes made or event not found";
    }

    $stmt->close();
    $conn->close();
}
