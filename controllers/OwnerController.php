<?php
require_once '../config/db.php';
require_once '../helpers/session.php';
require_once '../models/Owner.php';

$model = new Owner($pdo);

function handleFileUpload($file) {
    $upload_dir = __DIR__ . "/../views/owners/uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error: " . $file['error']);
    }

    $filename = time() . "_" . basename($file['name']);
    $target_path = $upload_dir . $filename;

    if (is_uploaded_file($file['tmp_name'])) {
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return $filename;
        } else {
            die("Failed to move uploaded file.");
        }
    } else {
        die("Not a valid uploaded file.");
    }

    return null;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $ow_id_file = isset($_FILES['ow_id']) ? handleFileUpload($_FILES['ow_id']) : null;

    $model->create([
        'district_id' => $_POST['district_id'],
        'full_name' => $_POST['full_name'],
        'ow_id' => $ow_id_file, // save only file name like "1720548853_ownerid.png"
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'created_by' => $_SESSION['user_id']
    ]);

    header("Location: ../views/owners/index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Fetch the current owner data to retain the old ow_id if no new file uploaded
    $existingOwner = $model->getById($_POST['id']);
    $ow_id_file = $existingOwner['ow_id']; // default to existing file

    // Check if new file is uploaded
    if (isset($_FILES['ow_id']) && $_FILES['ow_id']['error'] === UPLOAD_ERR_OK) {
        $ow_id_file = handleFileUpload($_FILES['ow_id']);
    }

    $model->update([
        'id' => $_POST['id'],
        'district_id' => $_POST['district_id'],
        'full_name' => $_POST['full_name'],
        'ow_id' => $ow_id_file,
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'address' => $_POST['address']
    ]);

    header("Location: ../views/owners/index.php");
    exit;
}




if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header("Location: ../views/owners/index.php");
    exit;
}
?>