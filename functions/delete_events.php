<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $email = $_SESSION['email'];

    $stmt = $conn->prepare("DELETE FROM user_events WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $id, $email);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
