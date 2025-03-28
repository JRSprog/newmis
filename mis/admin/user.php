<?php
// Start session
session_start();

// Include database connection
include '../connect.php'; // Ensure this file initializes $con securely


// Check if the user is logged in (if this page should only be accessible to logged-in users)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize inputs
    $fullname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['pass']; // Password will be hashed, so no need to sanitize

    // Validate required fields
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if email already exists
        $check_stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = 'Email already registered.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into the database using prepared statements
                $insert_stmt = $con->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sss", $fullname, $email, $hashed_password);
                    if ($insert_stmt->execute()) {
                        $success = '';
                        // Clear form fields
                        $fullname = $email = '';
                    } else {
                        $error = 'Database error: ' . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                } else {
                    $error = 'Database error: ' . $con->error;
                }
            }
            $check_stmt->close();
        } else {
            $error = 'Database error: ' . $con->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Account</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/user.css">
</head>
<body>

  <!-- Header -->
  <header>
    <div class="menu-container">
      <button class="burger-button" onclick="toggleSidebar()">☰</button>
    </div>
    <div class="dropdown">
      <button class="dropdown-button"><i class="fa-solid fa-user"></i></button>
      <div class="dropdown-content">
         <a href="../logout.php?logout=true"><i class="fa-solid fa-right-from-bracket"></i>&nbsp; Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="close">
        <span class="close-sidebar" onclick="toggleSidebar()"><i class="fa-solid fa-arrow-left"></i></span>
        <img src="../uploads/blogo.png" alt="Image" class="sidebar-image">
        <p class="sidebar-text"></p>
    </div>
    
    <div class="sidebar-content">
      <a href="dashboard.php" class="sidebar-item"><i class="fa-solid fa-house"></i>&nbsp; Dashboard</a>
      <a href="user.php" class="sidebar-item"><i class="fa-solid fa-user"></i>&nbsp; User</a>
      <a href="approval.php" class="sidebar-item"><i class="fa-solid fa-credit-card"></i>&nbsp; Online Approval</a>
      <a href="strecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Student Information</a>
      <a href="payrecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Payment Record</a>
      <a href="onfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
  </div>

  <div class="container">
    <?php if (isset($error)): ?>
      <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
      <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="auser">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        
        <label>Full name:</label>
        <input type="text" name="fname" value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        
        <label>Password (min 8 characters):</label>
        <input type="password" name="pass" required minlength="8"><br><br>
        
        <button type="submit" name="submit">Create Account</button>
      </form>
    </div>
    
    <div class="tuser">
      <table>
        <thead>
          <tr>
            <th>Full name</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // Fetch users from the database using mysqli
        $query = "SELECT fullname, email FROM users";
        $result = $con->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['fullname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="2">No users found.</td></tr>';
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="../js/script.js"></script>
</body>
</html>