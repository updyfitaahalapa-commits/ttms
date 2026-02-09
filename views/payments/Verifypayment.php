<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$plate_number = $_POST['plate_number'] ?? null;
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

<h3>Search Vehicle for Tax Payment</h3>
<form class="row g-3 mb-4" method="POST" action="">
    <div class="col-md-6">
        <input type="text" name="plate_number" class="form-control" placeholder="Enter Plate Number" required value="<?= htmlspecialchars($plate_number ?? '') ?>">
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $plate_number && !$vehicle): ?>
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
                <div class="alert alert-danger">There are unpaid taxes for this vehicle. Total Due: $<?= number_format($totalUnpaid, 2) ?></div>
            <?php else: ?>
                <div class="alert alert-success">No unpaid taxes for this vehicle.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../layout/footer.php'; ?>
