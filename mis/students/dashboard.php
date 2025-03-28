<?php
// Start session and include database connection
session_start();
include '../connect.php'; // Ensure this file initializes $con securely


if (!isset($_SESSION['student_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch the logged-in student's ID from the session
$student_id = $_SESSION['student_id'];


// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Fetch announcements from DB
$fetchQuery = "SELECT * FROM dash ORDER BY date";
$fetchResult = mysqli_query($con, $fetchQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>


<style>
        .dash {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 90%;
            margin: 40px auto;
            text-align: center;
        }

        .dash h1 {
            font-size: 42px;
            color: #333;
        }

        .dashadd {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 20%;
            transition: background-color 0.3s ease;
        }

        .dashadd:hover {
            background-color: #0056b3;
        }
        
        .dash p {
            color: gray;
            font-size: 16px;
            margin-bottom: 50px;
            font-weight: bold;
        }

        ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
        }

        ul li {
            display: inline-block;
            margin: 10px;
            border-bottom: 2px solid #ddd;
            margin-bottom: 25px;
        }

        ul li img {
            width: 90%;
            height: 30%;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .close6 {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close6:hover {
            color: black;
        }

        .form1 label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .form1 input {
            padding: 10px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form1 button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form1 button:hover {
            background-color: #218838;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dash {
                width: 90%;
            }

            .dashadd {
                width: 50%;
            }

            .modal-content {
                width: 90%;
            }
        }
</style>

  <!-- Header -->
  <header>
    <div class="menu-container">
      <button class="burger-button" onclick="toggleSidebar()">☰</button>
    </div>
    <div class="dropdown">
      <button class="dropdown-button"><i class="fa-solid fa-user"></i></button>
      <div class="dropdown-content">
          <a href="profile.php"><i class="fa-solid fa-user"></i>&nbsp; Profile</a>
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
      <a href="accstate.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Account Statement</a>
      <a href="stonfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
  </div>

<div class="main-content">
<div class="dash">
        <h1>Announcements</h1><br><br>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($fetchResult)) { ?>
                <li>
                    <img src="../<?php echo htmlspecialchars($row['img']); ?>" alt="Announcement">
                    <p><?php echo date('F j, Y - h:i A', strtotime($row['date'])); ?></p> 
                </li>
            <?php } ?>
        </ul>
    </div>
<script>
// Set current date/time as default
window.onload = function() {
    const now = new Date();
    const formattedDate = now.toISOString().slice(0, 16);
    document.getElementById('datetime').value = formattedDate;
}

</script>
<script src="../js/script.js"></script>
</body>
</html>