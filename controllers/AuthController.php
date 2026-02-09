<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/tax_helpers.php'; // include your renewAllExpiredTaxes() function here

ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status='Active' AND FinishDate IS NULL");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($password === $user['password']) {
                // ✅ Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $role_id = $_SESSION['role_id'] ?? null;
                renewAllExpiredTaxes($pdo, $user['id']);
                if ($role_id==1 || $role_id==2) 
                {
                    header("Location: ../views/dashboard/home.php");
                }
                else
                {
                    header("Location: ../views/payments/Verifypayment.php");
                }
                
                exit;
            } else {
                error_log("Invalid password for user: $username");
                echo "<div class='alert alert-danger text-center'>Invalid username or password.</div>";
            }
        } else {
            error_log("User not found: $username");
            echo "<div class='alert alert-danger text-center'>Invalid username or password.</div>";
        }
    }

    // ✅ Logout handler
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("Location: ../views/auth/login.php");
        exit;
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo "<div class='alert alert-danger text-center'>An internal error occurred. Please check logs.</div>";
}
?>
