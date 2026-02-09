<?php
require_once '../../config/db.php';
require_once '../layout/header.php';
require_once '../layout/navbar.php';

// Initialize values from POST (not GET)
$vehicle_type   = $_POST['vehicle_type'] ?? '';
$plate_number   = $_POST['plate_number'] ?? '';
$vehicle_status = $_POST['vehicle_status'] ?? '';
$start_date     = $_POST['start_date'] ?? '';
$end_date       = $_POST['end_date'] ?? '';
$tax_status     = $_POST['tax_status'] ?? '';

// Load dropdown values
$vehicleTypes = $pdo->query("SELECT vehicle_type FROM vehicles WHERE FinishDate IS NULL GROUP BY vehicle_type")->fetchAll(PDO::FETCH_COLUMN);
$vehicleStatuses = $pdo->query("SELECT status FROM vehicles WHERE FinishDate IS NULL GROUP BY status")->fetchAll(PDO::FETCH_COLUMN);

// Build query
$sql = "SELECT v.plate_number, v.vehicle_type, v.model, v.status AS vehicle_status,
               t.start_date, t.end_date, t.status AS tax_status,
               tt.name AS tax_type, tt.amount
        FROM vehicles v
        LEFT JOIN taxes t ON t.vehicle_id = v.id
        LEFT JOIN tax_types tt ON tt.id = t.tax_type_id
        WHERE v.FinishDate IS NULL";

$params = [];

if (!empty($vehicle_type)) {
    $sql .= " AND v.vehicle_type = ?";
    $params[] = $vehicle_type;
}
if (!empty($plate_number)) {
    $sql .= " AND v.plate_number LIKE ?";
    $params[] = "%$plate_number%";
}
if (!empty($vehicle_status)) {
    $sql .= " AND v.status = ?";
    $params[] = $vehicle_status;
}
if (!empty($start_date)) {
    $sql .= " AND t.start_date >= ?";
    $params[] = $start_date;
}
if (!empty($end_date)) {
    $sql .= " AND t.end_date <= ?";
    $params[] = $end_date;
}
if (!empty($tax_status)) {
    $sql .= " AND t.status = ?";
    $params[] = $tax_status;
}

$sql .= " ORDER BY t.start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
  <h4>Taxes Report</h4>
  <form id="filterForm" method="POST" class="row g-3 mb-4">
    <div class="col-md-2">
      <select name="vehicle_type" class="form-control">
        <option value="">All Vehicle Types</option>
        <?php foreach ($vehicleTypes as $type): ?>
          <option value="<?= $type ?>" <?= $vehicle_type == $type ? 'selected' : '' ?>><?= $type ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <input type="text" name="plate_number" class="form-control" placeholder="Plate Number" value="<?= htmlspecialchars($plate_number) ?>">
    </div>

    <div class="col-md-2">
      <select name="vehicle_status" class="form-control">
        <option value="">All Vehicle Status</option>
        <?php foreach ($vehicleStatuses as $status): ?>
          <option value="<?= $status ?>" <?= $vehicle_status == $status ? 'selected' : '' ?>><?= $status ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
    </div>

    <div class="col-md-2">
      <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
    </div>

    <div class="col-md-2">
      <select name="tax_status" class="form-control">
        <option value="">All Tax Status</option>
        <option value="paid" <?= $tax_status == 'paid' ? 'selected' : '' ?>>Paid</option>
        <option value="unpaid" <?= $tax_status == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      </select>
    </div>
  </form>
  <div class="mt-3">
    <button class="btn btn-success" onclick="printReport()">
      <i class="fas fa-print"></i> Print Report
    </button>
  </div>
  <table class="table table-bordered table-striped" id="dtable">
    <thead>
      <tr>
        <th>Plate Number</th>
        <th>Vehicle Type</th>
        <th>Model</th>
        <th>Vehicle Status</th>
        <th>Tax Type</th>
        <th>Amount</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Tax Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['plate_number']) ?></td>
          <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
          <td><?= htmlspecialchars($row['model']) ?></td>
          <td><?= htmlspecialchars($row['vehicle_status']) ?></td>
          <td><?= htmlspecialchars($row['tax_type']) ?></td>
          <td><?= htmlspecialchars($row['amount']) ?></td>
          <td><?= htmlspecialchars($row['start_date']) ?></td>
          <td><?= htmlspecialchars($row['end_date']) ?></td>
          <td><?= htmlspecialchars($row['tax_status']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  new DataTable('#dtable');

  const form = document.getElementById('filterForm');
  form.querySelectorAll('input, select').forEach(el => {
    el.addEventListener('change', () => {
      form.submit();
    });
  });
});


function printReport() {
  const tableHTML = document.querySelector('#dtable').outerHTML;

  const styles = `
    <style>
      body { font-family: Arial; padding: 20px; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 1px solid #444; padding: 8px; text-align: left; }
      th { background-color: #f2f2f2; }
      h4 { text-align: center; margin-bottom: 20px; }
    </style>
  `;

  const win = window.open('', '', 'width=900,height=700');
  win.document.write('<html><head><title>Tax Report</title>' + styles + '</head><body>');
  win.document.write('<h4>Taxes Report</h4>');
  win.document.write(tableHTML);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  win.print();
  win.close();
}

</script>

<?php require_once '../layout/footer.php'; ?>
