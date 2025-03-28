<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include '../connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;
        
        if (in_array($_FILES['img']['type'], $allowed_types) && $_FILES['img']['size'] <= $max_size) {
            $img_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($_FILES['img']['name']));
            $img_path = "../uploads/" . uniqid() . '_' . $img_name;

            if (move_uploaded_file($_FILES['img']['tmp_name'], $img_path)) {
                $img_db_path = "uploads/" . basename($img_path);
                $date = $_POST['date'];

                if (DateTime::createFromFormat('Y-m-d\TH:i', $date)) {
                    $stmt = $con->prepare("INSERT INTO dash (img, date) VALUES (?, ?)");
                    $stmt->bind_param("ss", $img_db_path, $date);
                    
                    if ($stmt->execute()) {
                        $success_message = "Announcement added successfully!";
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Invalid date format.";
                }
            } else {
                $error_message = "File upload failed!";
            }
        } else {
            $error_message = "Invalid file type or size (max 5MB).";
        }
    } else {
        $error_message = "Please select a valid image.";
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Fetch announcements
$fetchResult = mysqli_query($con, "SELECT * FROM dash ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/style.css?=v1.2">
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-hover: #3a56d4;
      --success-color: #4cc9f0;
      --success-hover: #38b6db;
      --text-color: #2b2d42;
      --text-light: #8d99ae;
      --background: aliceblue;
      --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      margin: 0;
      padding: 0;
      background-color: var(--background);
      color: var(--text-color);
      line-height: 1.6;
    }

    /* Dashboard Styles */
    .dash {
      background-color: #F5F5F5;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      width: 95%;
      margin: 2rem auto;
      text-align: center;
    }

    .dash h1 {
      font-size: 2.5rem;
      color: var(--text-color);
      margin-bottom: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .dash h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--success-color));
    }

    .dashadd {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0.8rem 1.8rem;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      margin-bottom: 2rem;
      transition: var(--transition);
      font-weight: 600;
    }

    .dashadd:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
    }

    /* Vertical Column Layout for Announcements */
    .announcements-column {
      display: flex;
      flex-direction: column;
      gap: 2rem;
      width: 100%;
    }

    .announcement-item {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
      transition: var(--transition);
    }

    .announcement-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    .announcement-image {
      width: 100%;
      height: auto;
      max-height: 400px;
      object-fit: contain;
    }

    .announcement-date {
      color: var(--text-light);
      font-size: 1rem;
      padding: 1rem;
      text-align: center;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 2000;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal.show {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background-color: white;
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 500px;
      position: relative;
      transform: translateY(-20px);
      transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
      transform: translateY(0);
    }

    .close6 {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 1.8rem;
      cursor: pointer;
      color: #aaa;
      transition: var(--transition);
    }

    .close6:hover {
      color: var(--text-color);
      transform: rotate(90deg);
    }

    /* Form Styles */
    .form1 label {
      display: block;
      margin-bottom: 0.5rem;
      text-align: left;
    }

    .form1 input {
      width: 100%;
      padding: 0.8rem;
      margin-bottom: 1.5rem;
      border: 1px solid #ddd;
      border-radius: 8px;
    }

    .form1 button {
      background-color: var(--success-color);
      color: white;
      border: none;
      padding: 0.8rem;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      transition: var(--transition);
    }

    .form1 button:hover {
      background-color: var(--success-hover);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .sidebar {
        width: 250px;
        left: -250px;
      }
      
      .sidebar.active + .main-content {
        margin-left: 250px;
      }
      
      .dash h1 {
        font-size: 2rem;
      }
      
      .announcement-image {
        max-height: 300px;
      }
    }

    @media (max-width: 576px) {
      .dash {
        padding: 1.5rem;
      }
      
      .modal-content {
        padding: 1.5rem;
      }
      
      .announcement-image {
        max-height: 250px;
      }
    }
  </style>
</head>
<body>
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

  <!-- Main Content -->
  <div class="main-content">
    <div class="dash">
      <h1><i class="fa-solid fa-bullhorn"></i> Announcements</h1><br><br>
      <button class="dashadd" id="openModal">Add Announcement</button><br><br>
      
      <div class="announcements-column">
        <?php while ($row = mysqli_fetch_assoc($fetchResult)): ?>
          <div class="announcement-item">
            <img src="../<?= htmlspecialchars($row['img']) ?>" alt="Announcement" class="announcement-image">
            <p class="announcement-date">
              <i class="far fa-calendar-alt"></i> <?= date('F j, Y', strtotime($row['date'])) ?>
              <br>
              <i class="far fa-clock"></i> <?= date('h:i A', strtotime($row['date'])) ?>
            </p>
          </div><br>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close6">&times;</span>
      <h2>Add Announcement</h2>
      <form class="form1" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label for="img">Image:</label>
        <input type="file" id="img" name="img" accept="image/jpeg, image/png, image/gif" required>
        <label for="datetime">Date & Time:</label>
        <input type="datetime-local" id="datetime" name="date" required>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>

  <script>
    // Toggle sidebar
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('active');
      document.body.classList.toggle('sidebar-active');
    }

    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('myModal');
      const openBtn = document.getElementById('openModal');
      const closeBtn = document.querySelector('.close6');
      const body = document.body;

      function openModal() {
        modal.classList.add('show');
        body.style.overflow = 'hidden';
      }

      function closeModal() {
        modal.classList.remove('show');
        body.style.overflow = 'auto';
      }

      openBtn.addEventListener('click', openModal);
      closeBtn.addEventListener('click', closeModal);

      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
          closeModal();
        }
      });

      // Set current datetime
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      document.getElementById('datetime').value = now.toISOString().slice(0, 16);
      
      <?php if (isset($success_message)): ?>
        alert("<?= $success_message ?>");
      <?php elseif (isset($error_message)): ?>
        alert("<?= $error_message ?>");
      <?php endif; ?>
    });
  </script>
  <script src="../js/script.js"></script>
</body>
</html>