<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId']; // Changed from studentId to userId

    $title = $_POST['title'] ?? '';
    if (strlen($title) > 255) {
        echo json_encode(["error" => "Title must not exceed 255 characters"]);
        exit;
    }

    $event_date = $_POST['start'] ?? '';
    if (!DateTime::createFromFormat('Y-m-d H:i:s', $event_date)) {
        echo json_encode(["error" => "Invalid event date format"]);
        exit;
    }


    if (empty($title) || empty($event_date)) {
        echo json_encode(["error" => "Title and event date are required"]);
        exit;
    }

    // Check if userId exists in users table
    $check_sql = "SELECT userId FROM users WHERE userId = ?"; // Updated query

    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $userId);

    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Invalid userId"]);
        exit;
    }
    $check_stmt->close();

    // Check if the event already exists for the user
    $check_event_sql = "SELECT * FROM user_events WHERE userId = ? AND title = ? AND event_date = ?";
    $check_event_stmt = $conn->prepare($check_event_sql);
    $check_event_stmt->bind_param("sss", $userId, $title, $event_date);
    $check_event_stmt->execute();
    $event_result = $check_event_stmt->get_result();

    if ($event_result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Event already exists"]);
        exit;
    }

    // Insert event
    $sql = "INSERT INTO user_events (userId, title, event_date) VALUES (?, ?, ?)"; // Updated query


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $userId, $title, $event_date);

    if ($stmt->execute()) {
        // Log success
        file_put_contents('event_log.txt', "Event added: ID " . $stmt->insert_id . "\n", FILE_APPEND);
        echo json_encode(["status" => "success", "message" => "Event added successfully", "id" => $stmt->insert_id]);

    } else {
        // Log error
        file_put_contents('event_log.txt', "Failed to add event: " . $stmt->error . "\n", FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "Failed to add event: " . $stmt->error]);

    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);

}
