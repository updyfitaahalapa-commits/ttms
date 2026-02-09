<?php
require_once '../config/db.php';

$vehicle_id = $_GET['vehicle_id'] ?? null;

if ($vehicle_id) {
    $stmt = $pdo->prepare("SELECT t.*, tt.name AS tax_type, tt.amount
                           FROM taxes t
                           LEFT JOIN tax_types tt ON tt.id = t.tax_type_id
                           WHERE t.vehicle_id = ?");
    $stmt->execute([$vehicle_id]);
    $taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($taxes) {
        $statusSums = [];

        echo "<table class='table table-bordered'>
              <thead>
                <tr>
                  <th>Tax Type</th>
                  <th>Amount</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>";

        foreach ($taxes as $t) {
            $amount = floatval($t['amount']);
            $status = $t['status'];

            // Accumulate totals by status
            if (!isset($statusSums[$status])) {
                $statusSums[$status] = 0;
            }
            $statusSums[$status] += $amount;

            echo "<tr>
                    <td>{$t['tax_type']}</td>
                    <td>$ " . number_format($amount, 2) . "</td>
                    <td>{$t['start_date']}</td>
                    <td>{$t['end_date']}</td>
                    <td>{$status}</td>
                  </tr>";
        }

        echo "</tbody><tfoot>";

        foreach ($statusSums as $status => $sum) {
            echo "<tr>
                    <th colspan='1' class='text-end'>Total ({$status})</th>
                    <th>$ " . number_format($sum, 2) . "</th>
                    <th colspan='3'></th>
                  </tr>";
        }

        echo "</tfoot></table>";

        // Show "Pay All" button if there are unpaid taxes
        if (!empty($statusSums['unpaid'])) {
            echo "
            <form method='POST' action='../../controllers/PayAllUnpaidTaxesController.php'>
                <input type='hidden' name='vehicle_id' value='{$vehicle_id}'>
                <input type='hidden' name='owner_id' value='{$taxes[0]['created_by']}'> <!-- Adjust if needed -->
                <button class='btn btn-success w-100 mt-3' name='pay_all'>
                    Pay All Unpaid Taxes ($ " . number_format($statusSums['unpaid'], 2) . ")
                </button>
            </form>";
        }
    } else {
        echo "<div class='text-muted'>No tax records found for this vehicle.</div>";
    }
} else {
    echo "<div class='text-danger'>Invalid vehicle ID.</div>";
}
?>
