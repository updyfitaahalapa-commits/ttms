<?php
require_once '../config/db.php';
require_once '../helpers/session.php';
require_once '../models/Vehicle.php';

$model = new Vehicle($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    try {
        $pdo->beginTransaction();

        // 1. Insert into vehicles
        $vehicleId = $model->create($pdo, [
            'owner_id'     => $_POST['owner_id'],
            'plate_number' => $_POST['plate_number'],
            'vehicle_type' => $_POST['vehicle_type'],
            'model'        => $_POST['model'],
            'status'       => $_POST['status'],
            'created_by'   => $_SESSION['user_id']
        ]);

        if (!$vehicleId) {
            throw new Exception("Failed to insert vehicle.");
        }

        // 2. Insert into taxes
        $stmt = $pdo->prepare("INSERT INTO taxes (
            vehicle_id, tax_type_id, start_date, end_date, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([
            $vehicleId,
            $_POST['tax_type_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            'unpaid',
            $_SESSION['user_id']
        ]);

        if (!$success) {
            throw new Exception("Failed to insert tax record.");
        }

        $taxId = $pdo->lastInsertId(); // Get newly inserted tax ID

        // 3. Log the creation in taxes_log
        $stmtLog = $pdo->prepare("INSERT INTO taxes_log (
            taxes_id, vehicle_id, tax_type_id, start_date, end_date, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmtLog->execute([
            $taxId,
            $vehicleId,
            $_POST['tax_type_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            'unpaid',
            $_SESSION['user_id']
        ]);

        $pdo->commit();
        header("Location: ../views/vehicles/index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Transaction failed: " . $e->getMessage());
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $pdo->beginTransaction();

        // 1. Update vehicles
        $model->update($pdo, [
            'id'            => $_POST['id'],
            'owner_id'      => $_POST['owner_id'],
            'plate_number'  => $_POST['plate_number'],
            'vehicle_type'  => $_POST['vehicle_type'],
            'model'         => $_POST['model'],
            'status'        => $_POST['status']
        ]);

        // 2. Update taxes
        $stmt = $pdo->prepare("UPDATE taxes SET 
            tax_type_id = ?, start_date = ?, end_date = ?
            WHERE id = ? AND vehicle_id = ?");

        $stmt->execute([
            $_POST['tax_type_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['tax_id'],
            $_POST['id']
        ]);

        // 3. Log the update in taxes_log
        $stmtLog = $pdo->prepare("INSERT INTO taxes_log (
            taxes_id, vehicle_id, tax_type_id, start_date, end_date, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmtLog->execute([
            $_POST['tax_id'],
            $_POST['id'],
            $_POST['tax_type_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            'unpaid', // or derive actual tax status if available
            $_SESSION['user_id']
        ]);

        $pdo->commit();
        header("Location: ../views/vehicles/index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Update failed: " . $e->getMessage());
    }
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
//     try {
//         $pdo->beginTransaction();

//         // 1. Update vehicles
//         $model->transfer($pdo, [
//             'id'            => $_POST['id'],
//             'new_owner_id'  => $_POST['new_owner_id']
//         ]);

//         $stmtvehicles_owner = $pdo->prepare("INSERT INTO vehicles_owner(vehicles_id,owner_id,new_owner_id,CreatedBy,RegDate) VALUES (?, ?, ?, ?, NOW())");

//         $stmtvehicles_owner->execute([
//             $_POST['id'],
//             $_POST['owner_id'],
//             $_POST['new_owner_id'],
//             $_SESSION['user_id']
//         ]);

//         $pdo->commit();
//         header("Location: ../views/vehicles/index.php");
//         exit;

//     } catch (Exception $e) {
//         $pdo->rollBack();
//         die("Update failed: " . $e->getMessage());
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $vehicleId = $_POST['id'];
    $currentOwnerId = $_POST['owner_id'];
    $newOwnerId = $_POST['new_owner_id'];

    if ($currentOwnerId == $newOwnerId) {
        $_SESSION['error'] = "Current and new owners are the same. Please select another.";
        header("Location: ../views/vehicles/index.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Update vehicle ownership
        $model->transfer($pdo, [
            'id'           => $vehicleId,
            'new_owner_id' => $newOwnerId
        ]);

        // 2. Insert into vehicles_owner log
        $stmtvehicles_owner = $pdo->prepare("
            INSERT INTO vehicles_owner(vehicles_id, owner_id, new_owner_id, CreatedBy, RegDate)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmtvehicles_owner->execute([
            $vehicleId,
            $currentOwnerId,
            $newOwnerId,
            $_SESSION['user_id']
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Vehicle transferred successfully.";
        header("Location: ../views/vehicles/index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Update failed: " . $e->getMessage());
    }
}


if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header("Location: ../views/vehicles/index.php");
    exit;
}

?>