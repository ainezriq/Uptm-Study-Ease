<?php
session_start();
include '../auth/conn.php';

if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Lecturer') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subjectId = $_POST['subject_id'];
    $noticeContent = $_POST['notice_content'];

    // Validate input
    if (empty($subjectId) || empty($noticeContent)) {
        echo json_encode(["status" => "error", "message" => "Subject and notice content are required."]);
        exit;
    }

    // Insert notice into the database
    $query = "INSERT INTO notices (userId, subject_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $_SESSION['userId'], $subjectId, $noticeContent);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Notice posted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
}
?>
