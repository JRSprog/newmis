<?php
// Start session
session_start();

// Include database connection
include '../connect.php'; // Ensure this file initializes $con securely


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    // Get and sanitize inputs
    $program = $_POST['select1'];
    $pname = $_POST['pname'];
    $paname = $_POST['paname'];
    $amount = (float)$_POST['amount'];
    $date = $_POST['date'];
    $misc = isset($_POST['misc']) ? (int)$_POST['misc'] : 0;
    $misc_text = isset($_POST['misc']) ? 'Miscellaneous' : null;

    try {
        $con->begin_transaction();
        
        // Get all student IDs for the selected program
        $studentQuery = $con->prepare("SELECT stid FROM students WHERE program = ?");
        $studentQuery->bind_param("s", $program);
        $studentQuery->execute();
        $result = $studentQuery->get_result();
        
        // Prepare the insert statement
        $stmt = $con->prepare("INSERT INTO fees (selct, pname, paname, amount, date, student_id, Miscellaneous) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        while ($row = $result->fetch_assoc()) {
            $student_id = $row['stid'];
            
            $stmt->bind_param("sssssis", $program, $pname, $paname, $amount, $date, $student_id, $misc_text);
            $stmt->execute();
        }
        
        // If miscellaneous is selected, update balances
        if ($misc > 0) {
            $updateStmt = $con->prepare("UPDATE students SET balance = balance + ? WHERE program = ?");
            $updateStmt->bind_param("is", $misc, $program);
            $updateStmt->execute();
        }
        
        $con->commit();
        $insertMessage = "success";
        
    } catch (Exception $e) {
        $con->rollback();
        $insertMessage = "error: " . $e->getMessage();
    } finally {
        if (isset($studentQuery)) $studentQuery->close();
        if (isset($stmt)) $stmt->close();
        if (isset($updateStmt)) $updateStmt->close();
    }
}


if (isset($_POST['submit1'])) {
    // Check if CSRF token exists in the POST request
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $namest = $_POST['namest'];
    $idst = $_POST['idst'];
    $progs = $_POST['progs'];
    $pname = $_POST['pname'];
    $paname = $_POST['paname'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $stid = ltrim($_POST['stid'], 's');

    $stmt = $con->prepare("SELECT stid FROM students WHERE stid = ?");
    $stmt->bind_param("s", $stid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        die("Error: Student ID '$stid' does not exist in the students table.");
    }
    $stmt->close();

    $stmt = $con->prepare("INSERT INTO stfees (pname, stid, program, payname, parname, amount, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssis", $namest, $stid, $progs, $pname, $paname, $amount, $date);

    if (!$stmt->execute()) {
        die("Error inserting into history: " . $stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Fees</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/styles.css">
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

<!-- Main Content -->
<div class="main-content">
    <div class="stfees">
        <h2>Ongoing Fees</h2>
        <div class="sep">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search" placeholder="Search ID/Student here...." onkeyup="searchStudents()">
            <button id="modal2" name="submit"><i class="fa-solid fa-plus"></i>&nbsp; Add fees</button>
        </div>

        <!-- Program Select Dropdown -->
        <form method="post">
        <select name="select" class="select" onchange="this.form.submit()">
    <option value="" disabled <?php echo (!isset($_POST['select']) || $_POST['select'] == '') ? 'selected' : ''; ?>>Select program</option>
    <option value="Bachelor of Science in Information Technology" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Information Technology') ? 'selected' : ''; ?>>Bachelor of Science in Information Technology</option>
    <option value="Bachelor of Science in Psychology" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Psychology') ? 'selected' : ''; ?>>Bachelor of Science in Psychology</option>
    <option value="Bachelor of Science in Criminology" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Criminology') ? 'selected' : ''; ?>>Bachelor of Science in Criminology</option>
    <option value="Bachelor of Science in Elementary Education" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Elementary Education') ? 'selected' : ''; ?>>Bachelor of Science in Elementary Education</option>
    <option value="Bachelor of Science in Business Administration" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Business Administration') ? 'selected' : ''; ?>>Bachelor of Science in Business Administration</option>
    <option value="Bachelor of Science in Tourism Management" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Tourism Management') ? 'selected' : ''; ?>>Bachelor of Science in Tourism Management</option>
    <option value="Bachelor of Science in Secondary Education" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Secondary Education') ? 'selected' : ''; ?>>Bachelor of Science in Secondary Education</option>
    <option value="Bachelor of Science in Physical Education" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Physical Education') ? 'selected' : ''; ?>>Bachelor of Science in Physical Education</option>
    <option value="Bachelor of Science in Computer Engineering" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Computer Engineering') ? 'selected' : ''; ?>>Bachelor of Science in Computer Engineering</option>
    <option value="Bachelor of Science in Entrepreneurship" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Entrepreneurship') ? 'selected' : ''; ?>>Bachelor of Science in Entrepreneurship</option>
    <option value="Bachelor of Science in Accounting Information System" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Accounting Information System') ? 'selected' : ''; ?>>Bachelor of Science in Accounting Information System</option>
    <option value="Bachelor of Science in Technological and Livelihood Education" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Technological and Livelihood Education') ? 'selected' : ''; ?>>Bachelor of Science in Technological and Livelihood Education</option>
    <option value="Bachelor of Science in Information Science" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Information Science') ? 'selected' : ''; ?>>Bachelor of Science in Information Science</option>
    <option value="Bachelor of Science in Office Administration" <?php echo (isset($_POST['select']) && $_POST['select'] == 'Bachelor of Science in Office Administration') ? 'selected' : ''; ?>>Bachelor of Science in Office Administration</option>
</select><br><br>
</form>
        <!-- Display Students Based on Selected Program -->
        <div id="studentList">
            <?php
             if (isset($_POST['select'])) {
                $choose = mysqli_real_escape_string($con, $_POST['select']);
                $display = "SELECT s.lname, s.fname, s.mname, s.stid, s.program, s.balance, MAX(h.date) as date
                            FROM students s
                            LEFT JOIN history h ON s.stid = h.studentId
                            WHERE s.program = '$choose'
                            GROUP BY s.stid ORDER BY lname"; 

                            $display1 = mysqli_query($con, $display);

                               if ($display1 && mysqli_num_rows($display1) > 0) {
                                     while ($rows = mysqli_fetch_assoc($display1)) {
                                     $date = new DateTime($rows['date']);
                                      $formattedDate = $date->format('F j, Y g:i A'); 
                        echo '<div class="parentstpay">';
                        echo '<div class="stpay">'; 
                        echo '<p class="studentName">Name of student: <strong>' . htmlspecialchars($rows['lname']) . ", " . htmlspecialchars($rows['fname']) . " " . htmlspecialchars($rows['mname']) . '</strong></p>';
                        echo '<p class="studentID">Student ID:<strong> s' . htmlspecialchars($rows['stid']) . '</strong></p>';
                        echo '<p class="studentProgram">Program:<strong> ' . htmlspecialchars($rows['program']) . '</strong></p>';
                        echo '<p>Name of payment:<strong> Miscellaneous</strong></p>';
                        echo '<p>Particular: <strong> Miscellaneous</strong> </p>';
                        echo '<p>Current Balance:<strong> ₱' . htmlspecialchars(number_format($rows['balance'])) . '</strong></p>';
                        echo '<p>Date updated:<strong> ' . $formattedDate . '</strong></p>';
                        echo '<button class="nadd" onclick="openModalForStudent(\'' . htmlspecialchars($rows['stid']) . '\', \'' . htmlspecialchars($rows['lname']) . '\', \'' . htmlspecialchars($rows['fname']) . '\', \'' . htmlspecialchars($rows['mname']) . '\', \'' . htmlspecialchars($rows['program']) . '\')">Add New Fees</button>';
                        echo '</div>';
                        echo '</div>';
                        

                    }
                } else {
                    echo "<p>No students found for the selected program.</p>";
                }
            }
            ?>


            <?php
             if (isset($_POST['select'])) {
                $choose1 = mysqli_real_escape_string($con, $_POST['select']);
                $display1 = "SELECT s.lname, s.fname, s.mname, s.stid, st.program, st.amount, st.payname, st.parname, MAX(st.date) as date
                            FROM students s
                            LEFT JOIN stfees st ON s.stid = st.stid
                            WHERE st.program = '$choose'
                            GROUP BY s.stid ORDER BY lname"; 

                            $display2 = mysqli_query($con, $display1);

                               if ($display1 && mysqli_num_rows($display2) > 0) {
                                     while ($rows1 = mysqli_fetch_assoc($display2)) {
                                     $date1 = new DateTime($rows1['date']);
                                      $formattedDate1 = $date1->format('F j, Y g:i A'); 
                        echo '<div class="parentstpay">';
                        echo '<div class="stpay">'; 
                        echo '<p class="studentName">Name of student: <strong>' . htmlspecialchars($rows1['lname']) . ", " . htmlspecialchars($rows1['fname']) . " " . htmlspecialchars($rows1['mname']) . '</strong></p>';
                        echo '<p class="studentID">Student ID:<strong> s' . htmlspecialchars($rows1['stid']) . '</strong></p>';
                        echo '<p class="studentProgram">Program:<strong> ' . htmlspecialchars($rows1['program']) . '</strong></p>';
                        echo '<p>Name of payment:<strong>' . htmlspecialchars($rows1['payname']) . '</strong></p>';
                        echo '<p>Particular: <strong>' . htmlspecialchars($rows1['parname']) . '</strong> </p>';
                        echo '<p>Amount:<strong> ₱' . htmlspecialchars(number_format($rows1['amount'])) . '</strong></p>';
                        echo '<p>Date release:<strong> ' . $formattedDate1 . '</strong></p>';
                        echo '</div>';
                        echo '</div>';
                        

                    }
                }
            }
            ?>


            <?php
             if (isset($_POST['select'])) {
                $choose2 = mysqli_real_escape_string($con, $_POST['select']);
                
                $display2 = "
                    SELECT students.stid, students.lname, students.fname, students.mname, students.program, 
                           fees.pname, fees.paname, fees.amount, fees.date
                    FROM students
                    LEFT JOIN fees ON students.stid = fees.student_id
                    WHERE students.program = '$choose2'
                ";

                $result = mysqli_query($con, $display2);
            
                if ($result && mysqli_num_rows($result) > 0) {
                    // Loop through the result and output the data
                    while ($rows2 = mysqli_fetch_assoc($result)) {
                        $date2 = new DateTime($rows2['date']);
                        $formattedDate2 = $date2->format('F j, Y g:i A');
                        
                        echo '<div class="parentstpay">';
                        echo '<div class="stpay">';
                        echo '<p class="studentName">Name of student: <strong>' . htmlspecialchars($rows2['lname']) . ", " . htmlspecialchars($rows2['fname']) . " " . htmlspecialchars($rows2['mname']) . '</strong></p>';
                        echo '<p class="studentID">Student ID: <strong>' . htmlspecialchars($rows2['stid']) . '</strong></p>';
                        echo '<p class="studentProgram">Program: <strong>' . htmlspecialchars($rows2['program']) . '</strong></p>';
                        echo '<p>Name of payment: <strong>' . htmlspecialchars($rows2['pname']) . '</strong></p>';
                        echo '<p>Particular: <strong>' . htmlspecialchars($rows2['paname']) . '</strong></p>';
                        echo '<p>Amount: <strong>₱' . htmlspecialchars(number_format($rows2['amount'])) . '</strong></p>';
                        echo '<p>Date release: <strong>' . $formattedDate2 . '</strong></p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } 
            }
            
            
              ?>
          </div>
        </div>

    <div id="myModal3" class="modal3">
     <div class="modal-content3">
        <span class="close3">&times;</span>
        <h2>Add Fees</h2>
        <form class="form" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="allst" value="">
            <select name="select1" class="select" onchange="this.form.submit()">
                 <option value="Select program" disabled>Select program</option>
                 <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                 <option value="Bachelor of Science in Psychology">Bachelor of Science in Psychology</option>
                 <option value="Bachelor of Science in Criminology">Bachelor of Science in Criminology</option>
                 <option value="Bachelor of Science in Elementary Education">Bachelor of Science in Elementary Education</option>
                 <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                 <option value="Bachelor of Science in Tourism Management">Bachelor of Science in Tourism Management</option>
                 <option value="Bachelor of Science in Secondary Education">Bachelor of Science in Secondary Education</option>
                 <option value="Bachelor of Science in Physical Education">Bachelor of Science in Physical Education</option>
                 <option value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</option>
                 <option value="Bachelor of Science in Entrepreneurship">Bachelor of Science in Entrepreneurship</option>
                 <option value="Bachelor of Science in Accounting Information System">Bachelor of Science in Accounting Information System</option>
                 <option value="Bachelor of Science in Technological and Livelihood Education">Bachelor of Science in Technological and Livelihood Education</option>
                 <option value="Bachelor of Science in Information Science">Bachelor of Science in Information Science</option>
                 <option value="Bachelor of Science in Office Administration">Bachelor of Science in Office Administration</option>
            </select><br>
           <label>Payment Name:</label>
           <input type="text" id="fee-name" name="pname" required>
           <label>Particular:</label>
           <input type="text" id="feee-name" name="paname" required>
           <label>Amount:</label>
           <input type="number" id="fee-amount" name="amount" required oninput="updateRadioValue()">
           <input type="radio" id="misc-radio" name="misc" value="" style="margin-left:-340px;">
           <label style="margin-top:-30px; margin-left: 20px;">miscellaneous</label><br>
           <label>Select Date and Time:</label>
           <input type="datetime-local" id="datetime3" name="date">
           <button type="submit" name="submit">Submit</button>
        </form>
    </div>
</div>

        <!-- Modal Structure -->
<div id="myModal4" class="modal4">
    <div class="modal-content4">
        <span class="close4">&times;</span>
        <h2>Add Fees</h2>
        <form class="form" method="post">
             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
             <label>Student Name:</label>
             <input type="text" id="stuname" name="namest" required>
             <label>Student ID:</label>
             <input type="text" id="stuid" name="idst" required>
             <label>Program:</label>
             <input type="text" id="prog" name="progs" required>
             <label>Payment Name:</label>
             <input type="text" id="fe-name" name="pname" required>
             <label>Particular:</label>
             <input type="text" id="fee-name" name="paname" required>
             <label>Amount:</label>
             <input type="number" id="fe-amount" name="amount" required>
             <label>Select Date and Time:</label>
             <input type="datetime-local" id="datetime1" name="date">
             <input type="hidden" id="hiddenStudentID1" name="stid">
             <button type="submit" name="submit1">Submit</button>
        </form>
    </div>
</div>

    </div>
</div>

<script>
   const modal3 = document.getElementById("myModal3");
   const btn = document.getElementById("modal2");
   const span = document.getElementsByClassName("close3")[0];

   btn.onclick = function() {
       modal3.style.display = "block"; 
   }
   span.onclick = function() {
       modal3.style.display = "none"; 
   }
   window.onclick = function(event) {
       if (event.target == modal3) {
           modal3.style.display = "none";
       }
   }
   document.addEventListener('DOMContentLoaded', function() {
        const today = new Date(); 
        const dd = String(today.getDate()).padStart(2, '0'); 
        const mm = String(today.getMonth() + 1).padStart(2, '0'); 
        const yyyy = today.getFullYear(); 

        const hours = String(today.getHours()).padStart(2, '0');
        const minutes = String(today.getMinutes()).padStart(2, '0'); 
        const formattedDateTime = `${yyyy}-${mm}-${dd}T${hours}:${minutes}`;
        document.getElementById('datetime3').value = formattedDateTime;
    });
 

  // SweetAlert for success and error messages after submitting fees
  <?php if (isset($insertMessage) && $insertMessage == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Fees Added',
      text: 'The new fees have been successfully added!',
    });
  <?php elseif (isset($insertMessage) && $insertMessage == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'There was an issue adding the fees. Please try again.',
    });
  <?php endif; ?>

  function searchStudents() {
    let input = document.getElementById("search").value.toLowerCase();
    let students = document.querySelectorAll(".stpay");

    students.forEach(function(student) {
        let studentName = student.querySelector(".studentName strong").textContent.toLowerCase();
        let studentID = student.querySelector(".studentID strong").textContent.toLowerCase();
        let program = student.querySelector(".studentProgram strong").textContent.toLowerCase();

        if (studentName.includes(input) || studentID.includes(input) || program.includes(input)) {
            student.style.display = "block"; // Show matching students
        } else {
            student.style.display = "none"; // Hide non-matching students
        }
    });
}

/// para modal student//////
function openModalForStudent(stid, lname, fname, mname, program) {
    const modal4 = document.getElementById("myModal4");
    modal4.style.display = "block";

    // Populate form fields
    document.getElementById("stuname").value = lname + ", " + fname + " " + mname;
    document.getElementById("stuid").value = "s" + stid;  // Showing "s" in the form for the user
    document.getElementById("prog").value = program;
    document.getElementById("hiddenStudentID1").value = stid; // Storing stid without the "s"
}

// On form submit, remove the "s" from stid
document.querySelector("form").onsubmit = function(event) {
    const stidWithPrefix = document.getElementById("hiddenStudentID1").value;
    const stidWithoutPrefix = stidWithPrefix.replace(/^s/, ''); // Remove 's' from the start

    document.getElementById("hiddenStudentID1").value = stidWithoutPrefix;  // Set the value without 's'
};

// Event listener to close modal
document.querySelector(".close4").onclick = function() {
    document.getElementById("myModal4").style.display = "none";
};

// Close modal when clicking outside
window.onclick = function(event) {
    let modal4 = document.getElementById("myModal4");
    if (event.target == modal4) {
        modal4.style.display = "none";
    }
};

// Set default datetime value on page load
window.onload = function() {
      const today = new Date(); 
      const dd = String(today.getDate()).padStart(2, '0'); 
      const mm = String(today.getMonth() + 1).padStart(2, '0'); 
      const yyyy = today.getFullYear(); 

      const hours = String(today.getHours()).padStart(2, '0');
      const minutes = String(today.getMinutes()).padStart(2, '0'); 
      const formattedDateTime = `${yyyy}-${mm}-${dd}T${hours}:${minutes}`;
      document.getElementById('datetime1').value = formattedDateTime;
  }

  function updateRadioValue() {
    const amountInput = document.getElementById('fee-amount');
    const miscRadio = document.getElementById('misc-radio');
    miscRadio.value = amountInput.value;
}
</script>

<script src="../js/script.js"></script>

</body>
</html>