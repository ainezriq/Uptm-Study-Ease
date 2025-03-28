<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['userId'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$userId = $_SESSION['userId'];

$sql = "SELECT eventId, title, event_date FROM user_events WHERE userId = ?"; // Updated query to include eventId

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);

$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $eventDate = date('Y-m-d H:i:s', strtotime($row['event_date'])); // Format for FullCalendar

    $events[] = [
        'id' => $row['eventId'], // Use eventId for unique identification
        'title' => $row['title'],
        'start' => $eventDate,
        'color' => '#a67c52' // Brown color for events
    ];
}

$stmt->close();
$conn->close();

echo json_encode($events);
