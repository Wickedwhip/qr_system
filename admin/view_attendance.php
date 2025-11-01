<?php
include '../includes/auth.php';
include '../includes/config.php';
date_default_timezone_set('Africa/Nairobi');

// Fetch all class sessions
$sessions = $conn->query("SELECT * FROM class_sessions ORDER BY created_at DESC");
?>

<h2>Class Attendance Overview</h2>

<?php while ($session = $sessions->fetch_assoc()): ?>
    <?php
    $start = strtotime($session['session_time']);
    $end = $start + ($session['duration_hours'] * 3600);
    $now = time();

// optional: log for debugging
// echo "DEBUG: now=".date("Y-m-d H:i:s", $now)." start=".date("Y-m-d H:i:s", $start)."<br>";


    if ($now > $end) {
        $status = "🔴 Expired";
        $remaining = "Ended";
    } else {
        $remaining_minutes = max(0, round(($end - $now) / 60)); // ensure no negatives
        $status = "🟢 Active";
        $remaining = "{$remaining_minutes} min left";
    }
    ?>
    <div style="border:1px solid #ccc; padding:15px; margin-bottom:15px; border-radius:10px;">
        <h3><?= htmlspecialchars($session['unit_name']) ?></h3>
        <p>
            <b>Start:</b> <?= $session['session_time'] ?><br>
            <b>Duration:</b> <?= $session['duration_hours'] ?> hour(s)<br>
            <b>Status:</b> <?= $status ?> (<?= $remaining ?>)
        </p>
        <img src="../qrcodes/<?= $session['qr_code'] ?>.png" width="120"><br><br>

        <table border="1" cellpadding="5" cellspacing="0" width="100%">
            <tr style="background:#f5f5f5;">
                <th>Admission No</th>
                <th>Name</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
            <?php
            $att_q = $conn->prepare("
                SELECT u.admission_no, u.name, a.time_in, a.status 
                FROM attendance a 
                JOIN users u ON a.student_id = u.id 
                WHERE a.session_id = ?
                ORDER BY a.time_in ASC
            ");
            $att_q->bind_param("i", $session['id']);
            $att_q->execute();
            $result = $att_q->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['admission_no']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['time_in']}</td>
                            <td>{$row['status']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No attendance yet</td></tr>";
            }
            ?>
        </table>
    </div>
<?php endwhile; ?>

