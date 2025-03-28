<?php
// Start session and include database connection
session_start();
include '../connect.php'; // Ensure this file initializes $con securely

// Check if the user is logged in and has a session ID (e.g., student ID)
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch student data from the database
$student_id = $_SESSION['student_id']; // Assuming student_id is stored in the session
$query = "SELECT * FROM students WHERE stid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc(); // Fetch student data as an associative array
} else {
    die("Student not found."); // Handle case where student data is not found
}

$stmt->close();

// Add 's' prefix to the student_id
$student['stid'] = 's' . $student['stid'];

// Handle New Password Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_query = "UPDATE students SET password = ? WHERE stid = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("si", $hashed_password, $student_id);

        if ($update_stmt->execute()) {
            $success = "Password updated successfully.";
        } else {
            $error = "Failed to update password.";
        }

        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change password</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/ch.css">
</head>
<body>

  <header>
    <div class="menu-container">
      <button class="burger-button" onclick="toggleSidebar()">☰</button>
    </div>
    <div class="dropdown">
      <button class="dropdown-button"><i class="fa-solid fa-user"></i></button>
      <div class="dropdown-content">
          <a href="#"><i class="fa-solid fa-user"></i>&nbsp; Profile</a>
          <a href="changepass.php"><i class="fa-solid fa-gear"></i>&nbsp; Settings</a>
          <a href="../logout.php?logout=true"><i class="fa-solid fa-right-from-bracket"></i>&nbsp; Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="close">
        <span class="close-sidebar" onclick="toggleSidebar()"><i class="fa-solid fa-arrow-left"></i></span>
        <img src="../uploads/blogo.png" alt="Image" class="sidebar-image">
        <p class="sidebar-text">Your text goes here.</p>
    </div>
    
    <div class="sidebar-content">
      <a href="dashboard.php" class="sidebar-item"><i class="fa-solid fa-house"></i>&nbsp; Dashboard</a>
      <a href="stonfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
  </div><br><br><br>

  <div class="content">
    <div class="items"></div>
    <h2>Set New Password</h2>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="set_password">Set Password</button>
    </form>
   </div>  
  </div>

  <script src="../js/script.js"></script>
</body>
</html>