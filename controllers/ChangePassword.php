<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 🔒 1. Ensure user is logged in
    if (!$user_id) {
        echo "<div class='alert alert-danger text-center'>You must be logged in to change your password.</div>";
        exit;
    }

    // 🔒 2. Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo "<div class='alert alert-danger text-center'>All fields are required.</div>";
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo "<div class='alert alert-danger text-center'>New passwords do not match.</div>";
        exit;
    }

    try {
        // 🔍 3. Fetch stored password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<div class='alert alert-danger text-center'>User not found.</div>";
            exit;
        }

        // 🔐 4. Compare current password (plain text)
        if ($current_password !== $user['password']) {
            echo "<div class='alert alert-danger text-center'>Current password is incorrect.</div>";
            exit;
        }

        // 💾 5. Update with new password (still plain text)
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($update->execute([$new_password, $user_id])) {
            session_unset();
            session_destroy();
            echo "<div class='alert alert-success text-center'>Password changed successfully. Redirecting to login...</div>";
            header("refresh:1;url=../views/auth/login.php"); // redirects after 3 seconds
            exit;

        } else {
            echo "<div class='alert alert-danger text-center'>Failed to update password.</div>";
        }

    } catch (Exception $e) {
        error_log("ChangePassword error: " . $e->getMessage());
        echo "<div class='alert alert-danger text-center'>An internal error occurred. Please try again later.</div>";
    }

} else {
    header("Location: ../views/dashboard/home.php");
    exit;
}
