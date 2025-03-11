<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$email = $_SESSION['email'];

$sql = "SELECT id, title, event_date FROM user_events WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $eventDate = date('Y-m-d\TH:i:s', strtotime($row['event_date'])); // Format: 2025-03-10T10:00:00

    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $eventDate,
        'color' => '#a67c52'
    ];
}

$stmt->close();
$conn->close();

echo json_encode($events);
