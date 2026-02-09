<?php
require_once '../config/db.php';
require_once '../helpers/session.php';
require_once '../models/User.php';

$model = new User($pdo);

// Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    try {
        $pdo->beginTransaction();

        $model->create([
            'full_name' => $_POST['full_name'],
            'username'  => $_POST['username'],
            'password'  => $_POST['password'],
            'email'     => $_POST['email'],
            'phone'     => $_POST['phone'],
            'role_id'   => $_POST['role_id'],
            'status'    => $_POST['status']
        ]);

        $pdo->commit();
        $_SESSION['success'] = "User created successfully.";
        header("Location: ../views/users/index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to create user: " . $e->getMessage();
        header("Location: ../views/users/index.php");
        exit;
    }
}

// Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $pdo->beginTransaction();

        $model->update($_POST['id'], [
            'full_name' => $_POST['full_name'],
            'username'  => $_POST['username'],
            'email'     => $_POST['email'],
            'phone'     => $_POST['phone'],
            'role_id'   => $_POST['role_id'],
            'status'    => $_POST['status']
        ]);

        $pdo->commit();
        $_SESSION['success'] = "User updated successfully.";
        header("Location: ../views/users/index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to update user: " . $e->getMessage();
        header("Location: ../views/users/index.php");
        exit;
    }
}

// Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $model->delete($_GET['id']);
    $_SESSION['success'] = "User deleted successfully.";
    header("Location: ../views/users/index.php");
    exit;
}

// Reset Password to Default (123) — Plain Text
if (isset($_GET['action']) && $_GET['action'] === 'reset_password' && isset($_GET['id'])) {
    try {
        $defaultPassword = '123'; // Plain text password

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$defaultPassword, $_GET['id']])) {
            $_SESSION['success'] = "Password reset to default (123) successfully.";
        } else {
            $_SESSION['error'] = "Failed to reset password.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error resetting password: " . $e->getMessage();
    }

    header("Location: ../views/users/index.php");
    exit;
}
?>
