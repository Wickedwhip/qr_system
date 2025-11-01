<?php
include '../includes/auth.php';
include '../includes/config.php';
date_default_timezone_set('Africa/Nairobi');

if (!isset($_GET['code'])) {
    echo "<h3>❌ Invalid QR code.</h3>";
    exit;
}

$qr_code = $_GET['code'];
$user_id = $_SESSION['user_id']; // current logged-in student

// Fetch session by QR code
$stmt = $conn->prepare("SELECT * FROM class_sessions WHERE qr_code = ?");
$stmt->bind_param("s", $qr_code);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    echo "<h3>❌ Invalid or unrecognized QR code.</h3>";
    exit;
}

// Check session time validity
$start = strtotime($session['session_time']);
$end = $start + ($session['duration_hours'] * 3600);
$now = time();

if ($now > $end) {
    echo "<h3>⚠️ This class session has expired. Attendance window closed.</h3>";
    exit;
}

// Check if already marked
$check = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND session_id = ?");
$check->bind_param("ii", $user_id, $session['id']);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    echo "<h3>⚠️ Attendance already marked for this session.</h3>";
    exit;
}

// Mark attendance
$time_in = date("Y-m-d H:i:s");
$status = "Present";

$insert = $conn->prepare("INSERT INTO attendance (student_id, session_id, time_in, status) VALUES (?, ?, ?, ?)");
$insert->bind_param("iiss", $user_id, $session['id'], $time_in, $status);

if ($insert->execute()) {
    echo "<h3>✅ Attendance successfully marked for: <b>{$session['unit_name']}</b><br>
          <small>Time: $time_in</small></h3>";
} else {
    echo "<h3>❌ Error marking attendance. Please try again.</h3>";
}
?>
