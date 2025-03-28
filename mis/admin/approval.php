  <?php
  // Simulan ang session
  session_start();

  // Isama ang database connection
  include '../connect.php'; // Siguraduhing secure ang initialization ng $con

  // I-enable ang error reporting para sa debugging
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Suriin kung naka-login ang user
  if (!isset($_SESSION['user_id'])) {
      // I-redirect sa login page kung hindi naka-login
      header("Location: ../login.php");
      exit();
  }

  // Generate ng CSRF token para sa seguridad
  if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  $updateMessage = '';

  // Kunin ang student ID mula sa URL kung mayroon
  if (isset($_GET['stid'])) {
      $id = intval($_GET['stid']); // Siguraduhing integer ang ID
  }

  // Kung may form submission para mag-update ng balance
  if (isset($_POST['submit'])) {
      // I-validate ang CSRF token
      if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
          die("CSRF token validation failed.");
      }

      // Kunin at i-sanitize ang mga input
      $id = intval($_POST['stid']); // Siguraduhing integer ang ID
      $nbalance = floatval($_POST['newBalance']); // Siguraduhing float ang balance

      // I-update ang balance ng estudyante gamit ang prepared statements
      $updateQuery = "UPDATE students SET balance = ? WHERE stid = ?";
      $stmt = mysqli_prepare($con, $updateQuery);
      mysqli_stmt_bind_param($stmt, 'di', $nbalance, $id);
      $res = mysqli_stmt_execute($stmt);

      if ($res) {
          $updateMessage = 'success'; // Tagumpay ang update
      } else {
          $updateMessage = 'error: ' . mysqli_error($con); // May error sa update
      }

      // I-insert ang history ng pagbabago
      $sels = $_POST['sel'];
      $new = floatval($_POST['cBalance']); // Siguraduhing float ang balance
      $date = $_POST['date'];

      // Suriin kung umiiral ang estudyante sa database
      $checkStudentQuery = "SELECT stid FROM students WHERE stid = ?";
      $stmt = mysqli_prepare($con, $checkStudentQuery);
      mysqli_stmt_bind_param($stmt, 'i', $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);

      if (mysqli_stmt_num_rows($stmt) == 0) {
          die("Error: Student ID '$id' does not exist in the students table.");
      }

      // I-insert ang data sa history table
      $insertQuery = "INSERT INTO history (sel, cbalance, date, studentId) VALUES (?, ?, ?, ?)";
      $stmt = mysqli_prepare($con, $insertQuery);
      mysqli_stmt_bind_param($stmt, 'sdsi', $sels, $new, $date, $id);
      $insert = mysqli_stmt_execute($stmt);

      if (!$insert) {
          die("Error inserting into history: " . mysqli_error($con));
      }
  }

  // Kung may action na isinumite (approve o reject)
  if (isset($_POST['action'])) {
    // Kunin at i-sanitize ang mga input
    $rname = mysqli_real_escape_string($con, $_POST['rname']);
    $rstid = mysqli_real_escape_string($con, $_POST['rstid']);
    $rparticular = mysqli_real_escape_string($con, $_POST['rparticular']);
    $ramount = floatval($_POST['ramount']);
    $rdate = mysqli_real_escape_string($con, $_POST['rdate']);
    $rtype = "Hma/Aub";
    
    $stid = intval($_POST['stid']);
    $status = ($_POST['action'] == 'approve') ? 'approved' : 'rejected';

    // INSERT TO RECORD TABLE ONLY IF APPROVED
    if ($_POST['action'] == 'approve') {
        $app = "approved";
        $stmt1 = $con->prepare("INSERT INTO record (name, stid, particular, amount, date, type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssdsss", $rname, $rstid, $rparticular, $ramount, $rdate, $rtype, $app);

        if ($stmt1->execute()) {
            $insertMessage = "success";
        } else {
            $insertMessage = "error: " . $stmt1->error;
        }
        $stmt1->close();
    }

    // UPDATE APPROVAL STATUS (FOR BOTH APPROVE AND REJECT)
    $updateStatusQuery = "UPDATE approval SET status = ? WHERE stid = ?";
    $stmt = mysqli_prepare($con, $updateStatusQuery);
    mysqli_stmt_bind_param($stmt, 'si', $status, $stid);
    $res = mysqli_stmt_execute($stmt);

    if ($res) {
        echo '<script>
        alert("Request has been ' . $status . '");
        window.location.href = "approval.php"; // Redirect after action
        </script>';
    } else {
        echo '<script>alert("Error updating status");</script>';
    }
}
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/styles.css?v=1.2">
    <!-- SweetAlert Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

  <div class="main-content">
    <div class="parent">
      <h1 style="text-align: center;">Approval Payment Online</h1>
      <?php
      // Kunin ang mga pending approvals mula sa database
      $kunin = "SELECT * FROM approval WHERE status = 'pending';";
      $hasa = mysqli_query($con, $kunin);
      while ($linya = mysqli_fetch_assoc($hasa)) {
          echo '<div class="child" id="child-' . htmlspecialchars($linya['stid']) . '">
              <br>';
          echo '<form method="post" action="" class="approval-form" onsubmit="return verifyAction(this);">';
          echo '<p>Name: <strong><input type="text" name="rname" value="' . htmlspecialchars($linya['name']) . '" readonly></strong></p>';
          echo '<p>Student ID: <strong><input type="text" name="rstid" value="' . htmlspecialchars($linya['stid']) . '" readonly></strong></p>';
          echo '<p>Particular: <strong><input type="text" name="rparticular" value="' . htmlspecialchars($linya['particular']) . '" readonly></strong></p>';
          echo '<p>Proof of Screenshot [click image]:</p>';
          echo '<img src="../uploads/h1.jpg" class="zoom-image" id="zoom-img">';
          echo '<p>Amount: <strong><input type="number" name="ramount" value="' . htmlspecialchars($linya['amount']) . '" readonly></strong></p>';
          $displayDate = date('F j, Y', strtotime($linya['date']));
          echo '<p>Date: <strong><input type="text" name="rdate_display" value="' . htmlspecialchars($displayDate) . '" readonly></strong></p>';
          
          echo '<input type="hidden" name="rdate" value="' . htmlspecialchars($linya['date']) . '">';
          echo '<input type="hidden" name="stid" value="' . htmlspecialchars($linya['stid']) . '">
                <button type="submit" name="action" value="approve" class="app">Approve</button>
                <button type="submit" name="action" value="reject" class="reject">Reject</button>
              </form>';
          echo '</div>';
      }
      ?>
    </div>

    <div class="form-container">
      <h1>Student Balance</h1>
      <div class="search-container1">
        <i class="fa-solid fa-magnifying-glass"></i><br><br>
        <input type="search" id="searchInput" placeholder="Search here...">
      </div>
      <table id="dataTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Lastname</th>
            <th>Firstname</th>
            <th>Middlename</th>
            <th>Balance</th>
            <th>Action</th>
          </tr>
        </thead>
        <?php
        // Kunin ang lahat ng estudyante mula sa database
        $select = "SELECT * FROM students";
        $sql = mysqli_query($con, $select);
        while ($row = mysqli_fetch_assoc($sql)) {
          echo '<tbody>';
          echo '<tr>';
          echo '<td>'.'s' . htmlspecialchars($row['stid']) . '</td>';
          echo '<td>' . htmlspecialchars($row['lname']) . '</td>';
          echo '<td>' . htmlspecialchars($row['fname']) . '</td>';
          echo '<td>' . htmlspecialchars($row['mname']) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['balance'])) . '</td>';
          echo '<td><button class="update" id="modal" data-id="' . $row['stid'] . '" data-stid="' . htmlspecialchars($row['stid']) . '" data-balance="' . htmlspecialchars($row['balance']) . '">Update Balance</button></td>';
          echo '</tr>';
          echo '</tbody>';
        }
        ?>
      </table>
    </div>

    <!-- Modal1 -->
    <div class="modal" id="updateModal">
      <div class="modal-content">
        <span class="close1" id="closeModal">&times;</span>
        <h2>Update Student Balance</h2><br>
        <form id="updateForm" method="post">
          <input type="hidden" id="studentId" name="id" required>
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <label>Student ID:</label>
          <input type="text" id="stid" name="stid" required readonly><br><br>

          <label>Particular :</label><br><br>
          <select name="sel" style="padding: 10px; width:80%;">
              <option value="">select here....</option>
              <option value="Prelim exam">Miscellaneous</option>
              <option value="Prelim exam">Prelim exam</option>
              <option value="Midterm exam">Midterm exam</option>
              <option value="Final exam">Final exam</option>
              <option value="Stage play[Philippines Stagers]">Stage play[Philippines Stagers]</option>
          </select><br><br>

          <label>Current Balance:</label>
          <input type="text" id="cBalance" name="cBalance" required readonly><br><br>

          <label>New Balance:</label>
          <input type="number" id="newBalance" name="newBalance" required><br><br>
            
          <input type="datetime-local" id="datetime" name="date"><br><br>

          <button type="submit" name="submit">Submit</button>
        </form>
      </div>
    </div>

    <!-- Image overlay for zoom -->
    <div class="overlay2" id="overlay2">
      <span class="close2" id="close2">&times;</span>
      <img class="overlay-image" id="overlay-image" />
    </div>
  </div>

  <script>
  // Function para i-verify ang action bago mag-submit ng form
  function verifyAction(form) {
      let confirmAction = confirm("Are you sure you want to proceed?");
      if (confirmAction) {
          return true; // Payagan ang form submission
      } else {
          alert("Action canceled.");
          return false; // Pigilan ang form submission
      }
  }

  // Kunin ang modal at mga button
  var updateModal = document.getElementById("updateModal");
  var updateClose = document.getElementById("closeModal");
  var editButtons = document.querySelectorAll(".update");

  editButtons.forEach(function(button) {
      button.addEventListener("click", function() {
          var stid = this.getAttribute("data-stid");
          var amount = this.getAttribute("data-balance");

          document.getElementById("stid").value = stid;
          document.getElementById("cBalance").value = amount; 
          updateModal.style.display = "block";
      });
  });
  updateClose.addEventListener("click", function() {
      updateModal.style.display = "none";
  });

  window.addEventListener("click", function(event) {
      if (event.target === updateModal) {
          updateModal.style.display = "none";
      }
  });

  <?php if ($updateMessage == 'success'): ?>
      Swal.fire({
          icon: 'success',
          title: 'Balance Updated',
          text: 'The student\'s balance has been successfully updated!',
      });
  <?php elseif ($updateMessage == 'error'): ?>
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'There was an issue updating the balance. Please try again.',
      });
  <?php endif; ?>

  // Function para i-set ang kasalukuyang petsa at oras
  window.onload = function() {
    const today = new Date(); 
    const dd = String(today.getDate()).padStart(2, '0'); 
    const mm = String(today.getMonth() + 1).padStart(2, '0'); 
    const yyyy = today.getFullYear(); 
    const hours = String(today.getHours()).padStart(2, '0');
    const minutes = String(today.getMinutes()).padStart(2, '0'); 
    const formattedDateTime = `${yyyy}-${mm}-${dd}T${hours}:${minutes}`;
    document.getElementById('datetime').value = formattedDateTime;
  }
  </script>

  <script src="../js/script.js"></script>
  </body>
  </html>