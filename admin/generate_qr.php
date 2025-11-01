<?php
include '../includes/auth.php';
include '../includes/config.php';
date_default_timezone_set('Africa/Nairobi');

require_once __DIR__ . '/../vendor/phpqrcode/qrlib.php';
if (!defined('QR_ECLEVEL_L')) define('QR_ECLEVEL_L', 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_name = $_POST['unit_name'];
    $session_time = $_POST['session_time'];
    $duration = (int)$_POST['duration_hours'];

    // Unique session code (used for DB + QR filename)
    $unique_code = "CLASS_" . strtoupper(preg_replace('/\s+/', '_', $unit_name)) . "_" . time();

    // Actual link encoded inside QR
    $base_url = "http://localhost/qr_system/student/scan_qr.php?code=" . urlencode($unique_code);

    // File path for QR image (just the code as filename)
    $filePath = "../qrcodes/" . $unique_code . ".png";

    // Generate QR image
    QRcode::png($base_url, $filePath, QR_ECLEVEL_L, 5);

    // Save to database
    $stmt = $conn->prepare("INSERT INTO class_sessions (unit_name, qr_code, session_time, duration_hours) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $unit_name, $unique_code, $session_time, $duration);
    $stmt->execute();

    echo "<script>alert('✅ Class QR created successfully!');</script>";
}
?>

<h2>Create Class QR</h2>
<form method="POST">
  <input type="text" name="unit_name" placeholder="Unit Name" required><br>
  <input type="datetime-local" name="session_time" required><br>
  <input type="number" name="duration_hours" placeholder="Duration (hours)" value="1" required><br>
  <button type="submit">Generate QR</button>
</form>

<h3>Active / Past Sessions</h3>
<table border="1" cellpadding="4">
<tr><th>Unit</th><th>Session Time</th><th>Duration</th><th>QR</th></tr>
<?php
$sessions = $conn->query("SELECT * FROM class_sessions ORDER BY id DESC");
while ($r = $sessions->fetch_assoc()) {
    echo "<tr>
        <td>{$r['unit_name']}</td>
        <td>{$r['session_time']}</td>
        <td>{$r['duration_hours']} hour(s)</td>
        <td><img src='../qrcodes/{$r['qr_code']}.png' width='100'></td>
    </tr>";
}
?>
</table>
