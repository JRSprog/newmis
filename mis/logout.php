<?php
session_start();
include 'connect.php';

function clearSessionToken($con, $table, $id_field, $id) {
    $stmt = $con->prepare("UPDATE $table SET session_token = NULL WHERE $id_field = ?");
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        error_log("Failed to clear session token for $id_field: $id");
    }
    $stmt->close();
}

if (isset($_SESSION['user_id'])) {
    clearSessionToken($con, 'users', 'id', $_SESSION['user_id']);
} elseif (isset($_SESSION['student_id'])) {
    clearSessionToken($con, 'students', 'stid', $_SESSION['student_id']);
}

// Destroy the session
session_unset();
session_destroy();

header("Location: login.php?logout=1");
exit;
?>