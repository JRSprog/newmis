<?php
session_start();
include '../connect.php';


// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php"); // Redirect to login page if not logged in
    exit;
}

// Get the logged-in student's ID from the session
$student_id = $_SESSION['student_id'];

// Fetch the student's balance from the database
$balance = 0; // Default balance
$query = "SELECT balance FROM students WHERE stid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

// Fetch the student's balance history from the database
$history = [];
$query = "SELECT cbalance, sel, date FROM history WHERE studentId = ? ORDER BY date DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Statement</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <p class="sidebar-text">Student ID: s<?php echo $student_id; ?></p>
    </div>
    
    <div class="sidebar-content">
      <a href="dashboard.php" class="sidebar-item"><i class="fa-solid fa-house"></i>&nbsp; Dashboard</a>
      <a href="accstate.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Account Statement</a>
      <a href="stonfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
  </div><br><br><br>

  <div class="maincontent">
    <div class="kulang">
        <h8>Current Balance</h8>
        <h9>₱ <?php echo number_format($balance); ?></h9> 
    </div>

    <button class="view" id="viewbal">Balance overview</button>
    <div class="tableb" id="tableb">
      <table>
        <thead>
          <tr>
            <th>Particular</th>
            <th>Balance</th>
            <th>Date updated</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $entry): ?>
            <tr>
              <td><?php echo htmlspecialchars($entry['sel']); ?></td>
              <td>₱ <?php echo number_format($entry['cbalance']); ?></td>
              <td><?php echo htmlspecialchars(date('F j, Y', strtotime($entry['date']))); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // JavaScript to toggle table visibility
    document.getElementById('viewbal').addEventListener('click', function() {
      var table = document.getElementById('tableb');
      if (table.style.display === 'none' || table.style.display === '') {
        table.style.display = 'block'; // Show the table
      } else {
        table.style.display = 'none'; // Hide the table
      }
    });
  </script>
  <script src="../js/script.js"></script>
</body>
</html>