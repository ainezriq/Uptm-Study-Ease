<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

if (!isset($_SESSION['studentId'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_SESSION['studentId'];
    $title = $_POST['title'] ?? '';
    $event_date = $_POST['start'] ?? '';

    if (empty($title) || empty($event_date)) {
        echo json_encode(["error" => "Title and event date are required"]);
        exit;
    }

    // Check if studentId exists in users table
    $check_sql = "SELECT studentId FROM users WHERE studentId = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $studentId);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Invalid studentId"]);
        exit;
    }
    $check_stmt->close();

    // Insert event
    $sql = "INSERT INTO user_events (studentId, title, event_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $studentId, $title, $event_date);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Event added successfully", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["error" => "Failed to add event: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
