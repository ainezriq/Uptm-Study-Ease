<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

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

    // Get user ID from studentId
    $stmt = $conn->prepare("SELECT id FROM users WHERE studentId = ?");
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows === 0) {
        $response["message"] = "User not found.";
        echo json_encode($response);
        exit;
    }

    $user = $userResult->fetch_assoc();
    $userId = $user['id'];
    $stmt->close();

    // Get existing enrollments
    $stmt = $conn->prepare("SELECT subject_id FROM enrollments WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $existingSubjects = [];
    while ($row = $result->fetch_assoc()) {
        $existingSubjects[] = $row['subject_id'];
    }
    $stmt->close();

    $insertedSubjects = 0;

    foreach ($subjects as $subjectCode) {
        $subjectCode = trim($subjectCode);

        // Get subject ID
        $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
        $stmt->bind_param("s", $subjectCode);
        $stmt->execute();
        $subjectResult = $stmt->get_result();

        if ($subjectResult->num_rows > 0) {
            $subject = $subjectResult->fetch_assoc();
            $subjectId = $subject['id'];

            // Insert only if not already enrolled
            if (!in_array($subjectId, $existingSubjects)) {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, subject_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $userId, $subjectId);
                if ($stmt->execute()) {
                    $insertedSubjects++;
                }
                $stmt->close();
            }
        }
    }

    if ($insertedSubjects > 0) {
        $response = ["status" => "success", "message" => "$insertedSubjects subject(s) added successfully."];
    } else {
        $response["message"] = "No new subjects were added.";
    }
} else {
    $response["message"] = "Invalid request.";
}

$conn->close();
echo json_encode($response);
