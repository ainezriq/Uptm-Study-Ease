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
            <input type="email" name="email" placeholder="Email" required>
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
            <input type="text" name="nric" placeholder="NRIC" required>
            <br>
            <input type="text" name="studentId" placeholder="Student ID" required>
            <br>
            <input type="text" name="userNo" placeholder="User Number" required>
            <br>
            <!-- Course Select -->
            <select name="course" required>
                <option value="" disabled selected>Select Course</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Cyber Security">Cyber Security</option>
                <option value="Early Childhood Education">Early Childhood Education</option>
                <option value="Human Resource">Human Resource</option>
            </select>
            <br>
            <select name="semester" required>
                <option value="" disabled selected>Select Semester</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
                <option value="4">Semester 4</option>
                <option value="5">Semester 5</option>
                <option value="6">Semester 6</option>
            </select>
            <br>
            <button type="submit" name="register">Sign Up</button>
            <p>Already have an account? <a href="#" onclick="showForm('login')">Login here</a></p>
        </form>

        <script>
            // Automatically detect userType based on email
            document.getElementById('email').addEventListener('input', function() {
                let email = this.value.toLowerCase();
                let userType = '';

                if (email.includes('cood')) {
                    userType = 'Lecturer';
                } else {
                    userType = 'Student';
                }

                document.getElementById('userType').value = userType;
            });
        </script>


    </div>
</body>

</html>