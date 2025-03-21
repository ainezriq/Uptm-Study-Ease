<?php
session_start();
include '../config/db.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['subjects']) && !empty($_SESSION['email'])) {
        $email = $_SESSION['email']; // Store email instead of user_id
        $subjects = $_POST['subjects'];

        foreach ($subjects as $subject) {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $subject, $email);
            $stmt->execute();
        }

        header("Location: ../home.php?status=success");
    } else {
        header("Location: ../home.php?status=error");
    }
}
?>
