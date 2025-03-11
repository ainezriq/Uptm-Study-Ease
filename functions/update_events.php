<?php
include '../auth/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $new_date = $_POST['new_date'];
    $email = $_SESSION['email'];

    $stmt = $conn->prepare("UPDATE user_events SET event_date = ? WHERE id = ? AND email = ?");
    $stmt->bind_param("sis", $new_date, $id, $email);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
