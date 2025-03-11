<?php
include '../auth/conn.php';

// Check if notice ID is provided
if (isset($_GET['id'])) {
    $noticeId = $_GET['id'];

    // Delete notice from database
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->bind_param("i", $noticeId);
    if ($stmt->execute()) {
        header("Location: ../inbox.php");
        exit();
    } else {
        echo "Error deleting notice.";
    }

    $stmt->close();
    $conn->close();
}
