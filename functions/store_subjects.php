<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

// Debugging: Log received POST data
file_put_contents("debug.log", print_r($_POST, true));

$response = ["status" => "error", "message" => "Unknown error"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['studentId'])) {
        $response["message"] = "Student ID not found.";
        echo json_encode($response);
        exit;
    }

    $studentId = $_SESSION['studentId'];
    $subjects = $_POST['subjects'] ?? [];

    if (!is_array($subjects) || empty($subjects)) {
        $response["message"] = "No subjects received.";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE studentId = ?");
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $existingSubjects = [];
    while ($row = $result->fetch_assoc()) {
        $existingSubjects[] = strtolower($row['subject_name']); // Case-insensitive comparison
    }
    $stmt->close();

    $insertedSubjects = 0;
    foreach ($subjects as $subject) {
        $subject = trim($subject);
        if (!empty($subject) && !in_array(strtolower($subject), $existingSubjects)) {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name, studentId) VALUES (?, ?)");
            $stmt->bind_param("ss", $subject, $studentId);
            if ($stmt->execute()) {
                $insertedSubjects++;
            } else {
                file_put_contents("debug.log", "Error inserting subject: " . $stmt->error . "\n", FILE_APPEND);
            }
            $stmt->close();
        }
    }

    if ($insertedSubjects > 0) {
        $response = ["status" => "success", "message" => "$insertedSubjects subject(s) saved successfully."];
    } else {
        $response["message"] = "No new subjects were added.";
    }
} else {
    $response["message"] = "Invalid request.";
}

$conn->close();
echo json_encode($response);
