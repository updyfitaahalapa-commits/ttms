<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_all'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $owner_id = $_POST['owner_id'];
    // $created_by = $_SESSION['user_id'];
    $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    try {
        $pdo->beginTransaction();

        // 1. Fetch all unpaid taxes for the vehicle
        $stmt = $pdo->prepare("
            SELECT t.id, t.tax_type_id, tt.amount
            FROM taxes t
            JOIN tax_types tt ON tt.id = t.tax_type_id
            WHERE t.vehicle_id = ? AND t.status = 'unpaid'
        ");
        $stmt->execute([$vehicle_id]);
        $unpaidTaxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$unpaidTaxes) {
            throw new Exception("No unpaid taxes found.");
        }

        // 2. Create payment record
        $totalAmount = array_sum(array_column($unpaidTaxes, 'amount'));
        $paymentStmt = $pdo->prepare("
            INSERT INTO payments (tax_id, amount_paid, payment_date, payment_method, transaction_id, created_by, owner_id)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");

        $logStmt = $pdo->prepare("
            INSERT INTO payments_log (payment_id, tax_id, amount_paid, payment_date, payment_method, transaction_id, created_by, owner_id)
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)
        ");

        foreach ($unpaidTaxes as $tax) {
            // insert into payments
            $transaction_id = uniqid('TXN_');
            $paymentStmt->execute([
                $tax['id'],
                $tax['amount'],
                'cash',           // adjust as needed
                $transaction_id,
                $created_by,
                $owner_id
            ]);

            $payment_id = $pdo->lastInsertId();

            // log into payments_log
            $logStmt->execute([
                $payment_id,
                $tax['id'],
                $tax['amount'],
                'cash',
                $transaction_id,
                $created_by,
                $owner_id
            ]);

            // update tax status
            $pdo->prepare("UPDATE taxes SET status = 'paid' WHERE id = ?")->execute([$tax['id']]);
        }

        $pdo->commit();
        if ($created_by!=0) 
        {
            header("Location: ../views/vehicles/index.php");
        }
        else
        {
            echo "<div class='alert alert-success text-center'>Payment successful. Thank you!</div>";
        }
        
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Payment error: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Payment failed: {$e->getMessage()}</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
}
