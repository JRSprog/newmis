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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/st.css">
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
  </div>

  <div class="main-content">
    <div class="prof">
        <h5>My Profile</h5>
        <label>Student ID</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['stid']); ?>">
        <label>Lastname</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['lname']); ?>">
        <label>Firstname</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['fname']); ?>">
        <label>Middlename</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['mname']); ?>">
        <label>Birthday</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['bday']); ?>">
        <label>Email</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['email']); ?>">
        <label>Address</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['address']); ?>">
        <label>Program</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['program']); ?>">
        <label>Year level</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($student['level']); ?>">
    </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>