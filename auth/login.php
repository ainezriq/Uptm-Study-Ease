<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim(strtolower($_POST['email']));
        $password = $_POST['password'];
        $nric = $_POST['nric'];
        $studentId = $_POST['studentId'];
        $userNo = $_POST['userNo'];
        $course = $_POST['course'];
        $semester = $_POST['semester'];

        // Automatically detect userType based on the email
        $userType = (strpos($email, 'cood') !== false) ? 'Lecturer' : 'Student';

        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>
                    alert('Email already exists! Try logging in.');
                    window.location.href = '../index.php';
                  </script>";
            exit();
        }

        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, nric, studentId, userNo, userType, course, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $email, $password, $nric, $studentId, $userNo, $userType, $course, $semester);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Registration successful! You can now log in.');
                    window.location.href = '../index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: Registration failed.');
                    window.location.href = '../index.php';
                  </script>";
        }

        $stmt->close();
        $conn->close();
    } elseif (isset($_POST['login'])) {
        session_start();
        $email = trim(strtolower($_POST['email']));
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT password, userType, course FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_password, $userType, $course);
            $stmt->fetch();

            if ($password === $db_password) {
                $_SESSION['email'] = $email;
                $_SESSION['userType'] = $userType;
                $_SESSION['course'] = $course;

                header("Location: ../home.php");
                exit();
            } else {
                echo "<script>
                alert('Invalid password! Please try again.');
                window.location.href = '../index.php';
              </script>";
            }
        } else {
            echo "<script>
            alert('User not found! Please register.');
            window.location.href = '../index.php';
          </script>";
        }

        $stmt->close();
        $conn->close();
    }
}
