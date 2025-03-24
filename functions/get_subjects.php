<?php
session_start();
include '../auth/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['studentId'])) {
    echo json_encode(["status" => "error", "message" => "Student not authenticated"]);
    exit;
}

$subjects = [];
$query = "SELECT subject_code, subject_name FROM subjects";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = ["code" => $row['subject_code'], "name" => $row['subject_name']];
    }
}

$conn->close();
echo json_encode(["status" => "success", "subjects" => $subjects]);
?>
