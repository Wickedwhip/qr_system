<?php
session_start();

// if not logged in, store where they were trying to go
if (!isset($_SESSION['id'])) {
    // Capture full URL (helps redirect back after login)
    $redirect_to = $_SERVER['REQUEST_URI'];
    $_SESSION['redirect_after_login'] = $redirect_to;

    header('Location: ../login.php');
    exit;
}
?>
