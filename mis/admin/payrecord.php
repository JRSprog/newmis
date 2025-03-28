<?php
// Start session
session_start();

// Include database connection
include '../connect.php';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle export requests
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $filename = "payment_records_" . date('Y-m-d');
    
    $select = "SELECT * FROM record ORDER BY date ASC";
    $result = mysqli_query($con, $select);
    
    if ($export_type === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename.xls");
        
        $data = "<table border='1'>";
        $data .= "<tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Particular</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Payment Type</th>
                  </tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            $data .= "<tr>
                        <td>s".$row['stid']."</td>
                        <td>".$row['name']."</td>
                        <td>".$row['particular']."</td>
                        <td>".$row['amount']."</td>
                        <td>".date('F j, Y', strtotime($row['date']))."</td>
                        <td>".$row['type']."</td>
                      </tr>";
        }
        
        $data .= "</table>";
        echo $data;
        exit();
        
    } elseif ($export_type === 'csv') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$filename.csv");
        
        $output = fopen("php://output", "w");
        fputcsv($output, array('Student ID', 'Name', 'Particular', 'Amount', 'Payment Date', 'Payment Type'));
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, array(
                's'.$row['stid'],
                $row['name'],
                $row['particular'],
                $row['amount'],
                date('F j, Y', strtotime($row['date'])),
                $row['type']
            ));
        }
        
        fclose($output);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Record</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
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
        <p class="sidebar-text">Your text goes here.</p>
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
    <div class="strecord">
      <h1>Payment Record</h1>
      <div class="search-container1">
        <i class="fa-solid fa-magnifying-glass"></i><br><br>
        <input type="search" id="searchInput" placeholder="Search here...">
        <button class="voice" id="recognition"><i class="fa-solid fa-microphone"></i></button>
      </div><br><br>  

      <div class="dropdown1">
        <button onclick="toggleDropdown1()" class="dl"><i class="fa-solid fa-download"></i>&nbsp; Download</button>
        <div id="downloadDropdown1" class="dropdown-content1">
          <a href="?export=excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
          <a href="?export=csv"><i class="fa-solid fa-file-csv"></i> CSV</a>
          <a href="#" onclick="printTable()"><i class="fa-solid fa-file-pdf"></i> PDF (Print)</a>
        </div>
      </div>
      
      <table id="dataTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Particular</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Payment type</th>
          </tr>
        </thead>
        <tbody>
          <?php
           $select = "SELECT * FROM record WHERE status = 'approved' ORDER BY date ASC";
            $result = mysqli_query($con, $select);
            while($row = mysqli_fetch_assoc($result)) {
              echo '<tr>';
              echo '<td>s'. htmlspecialchars($row['stid']) .'</td>';
              echo '<td>'. htmlspecialchars($row['name']) .'</td>';
              echo '<td>'. htmlspecialchars($row['particular']) .'</td>';
              echo '<td>'. htmlspecialchars($row['amount']) .'</td>';
              echo '<td>'. htmlspecialchars(date('F j, Y', strtotime($row['date']))) .'</td>';
              echo '<td>'. htmlspecialchars($row['type']) .'</td>';
              echo '</tr>';
            }
            ?> 
        </tbody>
      </table>
    </div>
  </div>

<script>
  // Toggle download dropdown
  function toggleDropdown1() {
    document.getElementById("downloadDropdown1").classList.toggle("show");
  }
  
  // Close the dropdown if clicked outside
  window.onclick = function(event) {
    if (!event.target.matches('.dl')) {
      var dropdowns = document.getElementsByClassName("dropdown-content1");
      for (var i = 0; i < dropdowns.length; i++) {
        var openDropdown = dropdowns[i];
        if (openDropdown.classList.contains('show')) {
          openDropdown.classList.remove('show');
        }
      }
    }
  }
  
  // Print table as PDF alternative
  function printTable() {
    var printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Payment Records</title>');
    printWindow.document.write('<style>table {border-collapse: collapse; width: 100%;} th, td {border: 1px solid #ddd; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h1>Payment Records</h1>');
    printWindow.document.write(document.getElementById("dataTable").outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
  }
  
  // Search functionality (existing code)
  document.getElementById('searchInput').addEventListener('keyup', function() {
    var input = this.value.toLowerCase();
    var rows = document.querySelectorAll('#dataTable tbody tr');
    var found = false;

    rows.forEach(function(row) {
      var cells = row.getElementsByTagName('td');
      var match = false;

      for (var i = 0; i < cells.length; i++) {
        if (cells[i].textContent.toLowerCase().indexOf(input) > -1) {
          match = true;
          found = true;
          break;
        }
      }

      row.style.display = match ? '' : 'none';
    });

    if (!found && input.trim() !== '') {
      speak("There is no data, boss.");
    }
  });

  // Voice command functionality (existing code)
  const recognitionButton = document.getElementById('recognition');
  const searchInput = document.getElementById('searchInput');

  if ('webkitSpeechRecognition' in window) {
    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    recognitionButton.addEventListener('click', () => {
      speak("Waiting for your command, boss.", () => {
        recognition.start();
        recognitionButton.innerHTML = '<i class="fa-solid fa-microphone-slash"></i>';
      });
    });

    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      searchInput.value = transcript;
      searchInput.dispatchEvent(new Event('keyup'));
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>';
    };

    recognition.onerror = (event) => {
      console.error('Voice recognition error:', event.error);
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>';
    };

    recognition.onend = () => {
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>';
    };
  } else {
    recognitionButton.style.display = 'none';
    console.warn('Your browser does not support the Web Speech API.');
  }

  function speak(text, callback) {
    if ('speechSynthesis' in window) {
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.voice = getFemaleVoice();
      utterance.rate = 1;
      utterance.pitch = 1;

      utterance.onend = () => {
        if (callback) callback();
      };

      speechSynthesis.speak(utterance);
    } else {
      console.warn('Your browser does not support speech synthesis.');
    }
  }

  function getFemaleVoice() {
    const voices = speechSynthesis.getVoices();
    const femaleVoice = voices.find(voice => voice.name.includes('Female') || voice.lang.includes('en-US'));
    return femaleVoice || voices[0];
  }

  window.speechSynthesis.onvoiceschanged = () => {
    console.log('Voices loaded');
  };
</script>
<script src="../js/script.js"></script>
</body>
</html>