<?php
session_start();
include 'connect.php'; // Ensure this file contains your database connection logic

$error_message = ""; // Variable to store error messages

// Login logic
if (isset($_POST['submit'])) {
    $identifier = $_POST['identifier']; // Can be email (for users) or stid (for students)
    $password = $_POST['password']; // Password for users or custom password for students

    // Validate input
    if (empty($identifier) || empty($password)) {
        $error_message = "Identifier and password are required.";
    } else {
        // Check if the identifier is an email (user login)
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // User login logic
            $stmt = $con->prepare("SELECT id, fullname, password, session_token FROM users WHERE email = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $fullname, $hashed_password, $session_token);

            if ($stmt->fetch()) {
                // Verify password
                if (password_verify($password, $hashed_password)) {
                    // Check if the user is already logged in on another device
                    if (!empty($session_token)) {
                        $error_message = "You are already logged in on another device.";
                    } else {
                        // Generate a unique session token
                        $new_session_token = bin2hex(random_bytes(32));

                        // Store the session token in the database
                        $update_stmt = $con->prepare("UPDATE users SET session_token = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $new_session_token, $id);
                        $update_stmt->execute();

                        // Store the session token in the user's session
                        $_SESSION['user_id'] = $id;
                        $_SESSION['session_token'] = $new_session_token;

                        // Redirect to dashboard on successful login
                        header("Location: ./admin/dashboard.php");
                        exit;
                    }
                } else {
                    $error_message = "Invalid Username or password.";
                }
            } else {
                $error_message = "Invalid Username or password.";
            }

            $stmt->close();
        } else {
            // Student login logic
            $stid = ltrim($identifier, 's'); // Remove 's' prefix from stid

            // Fetch student from the database
            $stmt = $con->prepare("SELECT stid, lname, password, session_token FROM students WHERE stid = ?");
            $stmt->bind_param("i", $stid);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($db_stid, $db_lname, $hashed_password, $session_token);

            if ($stmt->fetch()) {
                // Check if the student has a custom password (hashed password exists)
                if (!empty($hashed_password)) {
                    // Verify the entered password against the hashed password
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct
                        $login_successful = true;
                    } else {
                        $error_message = "Invalid Username or password.";
                    }
                } else {
                    // If no custom password, use the generated password logic
                    $expected_password = '#' . substr($db_lname, 0, 2) . '8080';

                    // Verify the entered password against the generated password
                    if ($password === $expected_password) {
                        // Password is correct
                        $login_successful = true;
                    } else {
                        $error_message = "Invalid Username or password.";
                    }
                }

                // If login is successful, proceed with session token logic
                if (isset($login_successful) && $login_successful) {
                    // Check if the student is already logged in on another device
                    if (!empty($session_token)) {
                        $error_message = "You are already logged in on another device.";
                    } else {
                        // Generate a unique session token
                        $new_session_token = bin2hex(random_bytes(32));

                        // Store the session token in the database
                        $update_stmt = $con->prepare("UPDATE students SET session_token = ? WHERE stid = ?");
                        $update_stmt->bind_param("si", $new_session_token, $db_stid);
                        $update_stmt->execute();

                        // Store the session token in the student's session
                        $_SESSION['student_id'] = $db_stid;
                        $_SESSION['session_token'] = $new_session_token;

                        // Redirect to dashboard on successful login
                        header("Location: ./students/dashboard.php");
                        exit;
                    }
                }
            } else {
                $error_message = "Invalid Username or password.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="shortcut icon" href="./uploads/blogo.png" type="x-icon">
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <style>
        .error-message {
    color: red;
    background-color: #ffe6e6;
    padding: 10px;
    border: 1px solid red;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
}
    </style>
    <div class="login">
        <form method="POST" action="">
            <img src="./uploads/blogo.png" alt="Logo">
            <p>Sign In</p>

            <!-- Display error message here -->
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="identifier" placeholder="Username" required>
            </div>
            
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye-slash" id="togglePassword"></i>
                </div>
            </div>
            
            <button type="submit" name="submit" class="button">Login</button><br><br>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("password");
        
        togglePassword.addEventListener("click", function() {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                togglePassword.classList.remove("fa-eye-slash");
                togglePassword.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                togglePassword.classList.remove("fa-eye");
                togglePassword.classList.add("fa-eye-slash");
            }
        });
    </script>
</body>
</html>