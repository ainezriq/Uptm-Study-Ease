<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');
include '../auth/conn.php';

// Debugging: Log received POST data
file_put_contents("debug.log", print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subjects']) && is_array($_POST['subjects']) && count($_POST['subjects']) > 0) {
        $studentId = $_SESSION['studentId'] ?? null;

        if (!$studentId) {
            echo "Error: Student ID not found.";
            exit;
        }

        foreach ($_POST['subjects'] as $subject) {
            if (!empty($subject)) {
                $stmt = $conn->prepare("INSERT INTO subjects (subject_name, studentId) VALUES (?, ?)");
                $stmt->bind_param("ss", $subject, $studentId);

                if (!$stmt->execute()) {
                    echo "Error: " . $stmt->error;
                    exit;
                }
                $stmt->close();
            }
        }

        echo "Subjects saved successfully.";
    } else {
        echo "Error: No subjects received.";
        exit;
    }
} else {
    echo "Error: Invalid request.";
    exit;
}

$conn->close();
?>
