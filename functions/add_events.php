<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $title = $_POST['title'] ?? '';
    $event_date = $_POST['start'] ?? '';

    if (empty($title) || empty($event_date)) {
        echo json_encode(["error" => "Title and event date are required"]);
        exit;
    }

    $sql = "INSERT INTO user_events (email, title, event_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $title, $event_date);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Event added successfully", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["error" => "Failed to add event"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
