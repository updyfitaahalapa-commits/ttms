<?php
    require_once '../config/db.php';
    require_once '../helpers/session.php';
    require_once '../models/TaxType.php';

    $model = new TaxType($pdo);

    // Create
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_POST['update'])) {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'amount'      => round((float)$_POST['amount']),
            'frequency' => $_POST['frequency'],
            'created_by' => $_SESSION['user_id'] ?? 0 // fallback to 1 if not set
        ];
        $model->create($data);
        header("Location: ../views/tax_types/index.php");
        exit;
    }

    // Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $data = [
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'amount'      => round((float)$_POST['amount']),
            'frequency' => $_POST['frequency']
        ];
        $model->update($data);
        header("Location: ../views/tax_types/index.php");
        exit;
    }

    // Delete
    if (isset($_GET['delete'])) {
        $model->delete($_GET['delete']);
        header("Location: ../views/tax_types/index.php");
        exit;
    }

?>