<?php
session_start();
$userType = $_SESSION['userType'] ?? 'Student'; // Default to Student
$userEmail = $_SESSION['email'] ?? ''; // Get logged-in user email
$userId = $_SESSION['userId'] ?? ''; // Get logged-in user ID

error_log("User ID: " . $userId); // Log user ID
error_log("User Type: " . $userType); // Log user type


// Database Connection
include 'auth/conn.php';

// Handle Notice Submission (Only for Lecturer)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userType == 'Lecturer') {
    $notice = $_POST['notice'] ?? ''; // Get notice content

    $subject_id = $_POST['subject_id'] ?? ''; // Get selected subject_id
    $filePath = ''; // Default file path to empty

    // Check if a file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];95

        // Specify directory to store the file
        $uploadDir = 'uploads/';

        // Make sure the uploads directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique name for the file to avoid conflicts
        $fileName = uniqid() . '-' . basename($fileName);
        $destination = $uploadDir . $fileName;

        // Move the uploaded file to the destination folder
        if (move_uploaded_file($fileTmpPath, $destination)) {
            $filePath = $destination;
        } else {
            echo "Error moving uploaded file!";
        }
    } else if ($_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        echo "File upload error: " . $_FILES['file']['error'];
    }

    error_log("User ID: " . $userId);
    error_log("Subject ID: " . $subject_id);
    error_log("Notice Content: " . $notice);

    if (!empty($notice)) { // Check if notice content is not empty

        $stmt = $conn->prepare("INSERT INTO notices (userId, subject_id, content, file_path, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $subject_id, $notice, $filePath);

        if ($stmt->execute()) {
            header("Location: inbox.php");
            exit();
        } else {
            echo "Error inserting into database: " . $stmt->error;
        }
    }
}
?>
