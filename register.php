<?php
include 'includes/config.php';
date_default_timezone_set('Africa/Nairobi');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // admin / student

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error: email already used');</script>";
    }
}
?>

<form method="POST" action="">
  <input type="text" name="name" placeholder="Full Name" required><br>
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <select name="role" required>
    <option value="">Select Role</option>
    <option value="admin">Admin</option>
    <option value="student">Student</option>
  </select><br>
  <button type="submit">Register</button>
</form>
