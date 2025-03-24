<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup</title>
    <link rel="stylesheet" href="styles/style.css">
    <script>
        function showForm(formType) {
            if (formType === 'login') {
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('register-form').style.display = 'none';
                document.getElementById('form-title').innerText = 'Login';
            } else {
                document.getElementById('login-form').style.display = 'none';
                document.getElementById('register-form').style.display = 'block';
                document.getElementById('form-title').innerText = 'Sign Up';
            }
        }
    </script>
</head>

<body class="login-body">
    <div class="login-container">
        <h1 id="form-title">Login</h1>

        <!-- Login Form -->
        <form id="login-form" class="login-form" action="auth/login.php" method="POST">
            <input type="text" name="studentId" placeholder="Student ID" required>
            <br>
            <input type="password" name="password" placeholder="Password" required>
            <br>
            <button type="submit" name="login">Login</button>
            <p>Don't have an account? <a href="#" onclick="showForm('register')">Register here</a></p>
        </form>

        <!-- Register Form -->
        <form id="register-form" class="login-form" action="auth/login.php" method="POST" style="display: none;">
            <input type="text" name="username" placeholder="Username" required>
            <br>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <br>
            <input type="password" name="password" placeholder="Password" required>
            <br>
            <input type="text" name="studentId" placeholder="Student ID" required>
<br>

            <!-- Course Select -->
            <select name="course" required>
                <option value="" disabled selected>Select Course</option>
                <option value="CC101 - Diploma in Computer Science">CC101 - Diploma in Computer Science</option>
                <option value="CM201 - Bachelor of Art in 3D Animation and Digital Media (Honours)">CM201 - Bachelor of Art in 3D Animation and Digital Media (HONOURS)</option>
                <option value="CT204 - Bachelor of Information Techonolgy (Honours) in Computer Application Development">CT204 - Bachelor of Information Techonolgy (Honours) in Computer Application Development</option>
                <option value="CT206 - Bachelor of Information Techonolgy (Honours) in Cyber Security">CT206 - Bachelor of Information Techonolgy (Honours) in Cyber Security</option>
            </select>
            <br>
            <!-- User Type Selection -->
            <select name="userType" required>
                <option value="" disabled selected>Select User Type</option>
                <option value="Student">Student</option>
                <option value="Lecturer">Lecturer</option>
            </select>
            <br>
            <button type="submit" name="register">Sign Up</button>
            <p>Already have an account? <a href="#" onclick="showForm('login')">Login here</a></p>
        </form>
    </div>
</body>

</html>