<?php
session_start();
$userType = $_SESSION['userType'] ?? 'Student'; // Default to Student
$userEmail = $_SESSION['email'] ?? ''; // Get logged-in user email
$userCourse = $_SESSION['course'] ?? 'All'; // Get student's course (assuming it's stored in session)

// Database Connection
include 'auth/conn.php';

// Handle Notice Submission (Only for Lecturer)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userType == 'Lecturer') {
    $notice = $_POST['notice'] ?? '';
    $subject_id = $_POST['subject_id'] ?? ''; // Get selected subject_id
    $filePath = ''; // Default file path to empty

    // Check if a file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

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
            echo "File uploaded successfully: " . $filePath; // Debug the file path
        } else {
            echo "Error moving uploaded file!";
        }
    } else {
        echo "File upload error: " . $_FILES['file']['error'];
    }

    // Insert notice into the database if there is content
    if (!empty($notice)) {
        $stmt = $conn->prepare("INSERT INTO notices (user_id, subject_id, content, file_path, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $subject_id, $notice, $filePath);

        if ($stmt->execute()) {
            header("Location: inbox.php");
            exit();
        } else {
            echo "Error inserting into database: " . $stmt->error;
        }
    }
}

// Fetch Notices based on User Type
if ($userType == 'Student') {
    // Fetch notices for the subjects the student is enrolled in
    $stmt = $conn->prepare("SELECT n.* FROM notices n 
                             JOIN enrollments e ON n.subject_id = e.subject_id 
                             WHERE e.userId = ? 
                             ORDER BY n.created_at DESC");

    $stmt->bind_param("i", $userId);

} else {
    // Lecturers can see all notices
    $stmt = $conn->prepare("SELECT * FROM notices ORDER BY created_at DESC");
}
$stmt->execute();
$notices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Course List for Lecturer
$courses = $conn->query("SELECT DISTINCT course FROM users WHERE course IS NOT NULL")->fetch_all(MYSQLI_ASSOC);

// Fetch Subjects for Lecturer
$subjects = $conn->query("SELECT subject_id, subject_name FROM subjects")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Notices</title>
    <link rel="stylesheet" href="styles/style.css">
    <script>
        function openNoticeForm() {
            document.getElementById("noticePopup").style.display = "block";
        }

        function closeNoticeForm() {
            document.getElementById("noticePopup").style.display = "none";
        }
    </script>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="website-name">UPTM Study Ease</span>
        </div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="hamburger">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu">
        <a href="home.php">Home</a>
        <a href="inbox.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Inbox Container -->
    <div class="inbox-container">
        <h2 style="margin-left: 20px;">Inbox</h2>

        <!-- Notices List -->
        <div class="notices">
            <?php foreach ($notices as $notice): ?>
                <div class="notice">
                    <p><?= htmlspecialchars($notice['content']) ?></p>
                    <small>Posted on: <?= $notice['created_at'] ?> | Subject: <?= $notice['subject_id'] ?></small>

                    <?php if (!empty($notice['file_path'])): ?>
                        <div class="file-link">
                            <a href="<?= htmlspecialchars($notice['file_path']) ?>" target="_blank">
                                <i class="fa fa-download"></i> Download File
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- For Lecturer: Both Download and Delete Buttons -->
                    <?php if ($userType == 'Lecturer'): ?>
                        <div class="actions">
                            <button class="delete-btn" onclick="deleteNotice(<?= $notice['id'] ?>)">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($notices)): ?>
                <p>No notices available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating + Button for Lecturers -->
    <?php if ($userType == 'Lecturer'): ?>
        <button class="floating-btn" onclick="openNoticeForm()">+</button>

        <!-- Popup Form for Adding Notices -->
        <div id="noticePopup" class="popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeNoticeForm()">×</span>
                <h3>Post Notice / Learning Materials</h3>
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="notice" placeholder="Enter notice..." required></textarea>
                    <label for="subject">Select Subject:</label>
                    <select name="subject_id" required>
                        <option value="">Select a subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject['subject_id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="file">Upload File (Optional):</label>
                    <input type="file" name="file" accept=".pdf, .docx, .jpg, .png, .txt">

                    <button type="submit">Post</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function openNoticeForm() {
            document.getElementById("noticePopup").style.display = "block";
        }

        function closeNoticeForm() {
            document.getElementById("noticePopup").style.display = "none";
        }

        function downloadNotice(content) {
            const element = document.createElement('a');
            const file = new Blob([content], {
                type: 'text/plain'
            });
            element.href = URL.createObjectURL(file);
            element.download = "notice.txt";
            document.body.appendChild(element);
            element.click();
        }

        function deleteNotice(noticeId) {
            if (confirm("Are you sure you want to delete this notice?")) {
                window.location.href = "functions/delete_notice.php?id=" + noticeId;
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            const hamburger = document.querySelector(".hamburger");
            const mobileMenu = document.querySelector(".mobile-menu");
            const navLinks = document.querySelector(".nav-links");

            hamburger.addEventListener("click", function() {
                mobileMenu.classList.toggle("active");
            });

            function checkScreenSize() {
                if (window.innerWidth > 768) {
                    mobileMenu.classList.remove("active");
                    navLinks.style.display = "flex";
                } else {
                    navLinks.style.display = "none";
                }
            }
            checkScreenSize();
            window.addEventListener("resize", checkScreenSize);
        });
    </script>

</body>

</html>
