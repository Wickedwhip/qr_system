<?php
include '../includes/auth.php';
include '../includes/config.php';
date_default_timezone_set('Africa/Nairobi');

$qr_code = $_GET['code'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scan Attendance</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    #reader { width: 400px; margin: 20px auto; }
    h2 { text-align: center; }
    body { font-family: Arial; text-align: center; }
  </style>
</head>
<body>
  <h2>Scan Attendance QR</h2>
  <div id="reader"></div>

  <?php if (!$qr_code): ?>
    <p style="color:red;">⚠️ No QR code detected — Camera Test Mode</p>
  <?php else: ?>
    <p style="color:green;">✅ QR Code Loaded: <?php echo htmlspecialchars($qr_code); ?></p>
  <?php endif; ?>

  <script>
  const qrCode = "<?php echo $qr_code ?? ''; ?>";

  function startScanner(cameraId = null) {
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: 250 };

    html5QrCode.start(
      cameraId || { facingMode: "environment" },
      config,
      (decodedText) => {
        console.log(`Decoded: ${decodedText}`);
        html5QrCode.stop();
        window.location.href = `mark_attendance.php?code=${encodeURIComponent(decodedText)}`;
      },
      (error) => { console.warn(error); }
    ).catch((err) => {
      console.error("Scanner failed to start:", err);
      alert("❌ Unable to start camera. Try allowing camera access or switching browsers.");
    });
  }

  Html5Qrcode.getCameras().then(devices => {
    if (devices && devices.length) {
      const cameraId = devices[0].id;
      startScanner(cameraId);
    } else {
      alert("❌ No camera found on this device.");
    }
  }).catch(err => {
    console.error("Camera error:", err);
    alert("❌ Camera access denied or unavailable.");
  });
  </script>
</body>
</html>
