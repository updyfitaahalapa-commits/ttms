<?php
session_start();
require_once '../../config/db.php';
// $user_id=$_SESSION['user_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$plate_number = $_GET['plate_number'] ?? null;
$vehicle = null;
$taxes = [];
$totalUnpaid = 0;

if ($plate_number) {
    // Fetch vehicle by plate number including owner_id (required for payment)
    $stmt = $pdo->prepare("SELECT id, owner_id, plate_number, model FROM vehicles WHERE plate_number = ?");
    $stmt->execute([$plate_number]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        // Fetch unpaid taxes for the vehicle
        $stmt = $pdo->prepare("
            SELECT t.*, tt.name AS tax_type, tt.amount
            FROM taxes t
            LEFT JOIN tax_types tt ON tt.id = t.tax_type_id
            WHERE t.vehicle_id = ? AND t.status = 'unpaid'
        ");
        $stmt->execute([$vehicle['id']]);
        $taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($taxes as $t) {
            $totalUnpaid += floatval($t['amount']);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Tax Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

<h3>Search Vehicle for Tax Payment</h3>

<form class="row g-3 mb-4" method="GET" action="">
    <div class="col-md-6">
        <input type="text" name="plate_number" class="form-control" placeholder="Enter Plate Number" required value="<?= htmlspecialchars($plate_number ?? '') ?>">
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

<?php if ($plate_number && !$vehicle): ?>
    <div class="alert alert-danger">No vehicle found with plate number: <strong><?= htmlspecialchars($plate_number) ?></strong></div>
<?php endif; ?>

<?php if ($vehicle): ?>
    <div class="card">
        <div class="card-header bg-info text-white">
            <strong>Vehicle:</strong> <?= htmlspecialchars($vehicle['plate_number']) ?> |
            <strong>Model:</strong> <?= htmlspecialchars($vehicle['model']) ?>
        </div>
        <div class="card-body">
            <?php if (!empty($taxes)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th>Amount</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taxes as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['tax_type']) ?></td>
                                <td>$ <?= number_format($t['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($t['start_date']) ?></td>
                                <td><?= htmlspecialchars($t['end_date']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($t['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Unpaid</th>
                            <th>$ <?= number_format($totalUnpaid, 2) ?></th>
                            <th colspan="3" class="text-end">
                                <!-- <form action="../../controllers/PayAllUnpaidTaxesController.php" method="POST" class="d-inline">
                                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                    <input type="hidden" name="owner_id" value="<?= $vehicle['owner_id'] ?>">
                                    <input type="hidden" name="pay_all" value="1">
                                    <button type="submit" class="btn btn-success">Pay All</button>
                                </form> -->
                                <form action="../../controllers/PayAllUnpaidTaxesController.php" method="POST" class="d-inline">
                                  <input type="hidden" name="pay_all" value="1">
                                  <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                  <input type="hidden" name="owner_id" value="<?= $vehicle['owner_id'] ?>">
                                  <input type="hidden" name="created_by" value="<?= $user_id?>"> 
                                  <button type="submit" class="btn btn-success">Pay All</button>
                                </form>

                            </th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <div class="alert alert-success">No unpaid taxes for this vehicle.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
