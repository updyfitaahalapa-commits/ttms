<?php
function renewAllExpiredTaxes(PDO $pdo, int $created_by): void
{
    $currentDate = date('Y-m-d');

    // Get the latest tax record for each vehicle, joined with its tax type (to get frequency)
    $stmt = $pdo->query("
        SELECT t.vehicle_id, t.tax_type_id, t.end_date, tt.frequency
        FROM taxes t
        INNER JOIN (
            SELECT vehicle_id, MAX(end_date) AS max_end_date
            FROM taxes
            GROUP BY vehicle_id
        ) latest ON t.vehicle_id = latest.vehicle_id AND t.end_date = latest.max_end_date
        INNER JOIN tax_types tt ON tt.id = t.tax_type_id
    ");

    $expiredTaxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiredTaxes as $tax) {
        // Check if expired
        if ($currentDate > $tax['end_date']) {
            $newStartDate = date('Y-m-d', strtotime($tax['end_date'] . ' +1 day'));

            // Calculate new end date based on frequency
            switch (strtolower($tax['frequency'])) {
                case 'daily':
                    $newEndDate = $newStartDate; // same day
                    break;
                case 'monthly':
                    $newEndDate = date('Y-m-d', strtotime($newStartDate . ' +1 month -1 day'));
                    break;
                case 'quarterly':
                    $newEndDate = date('Y-m-d', strtotime($newStartDate . ' +3 months -1 day'));
                    break;
                case 'half-yearly':
                    $newEndDate = date('Y-m-d', strtotime($newStartDate . ' +6 months -1 day'));
                    break;
                case 'yearly':
                default:
                    $newEndDate = date('Y-m-d', strtotime($newStartDate . ' +1 year -1 day'));
                    break;
            }

            // Optional: check if same range already exists to avoid duplicates

            $check = $pdo->prepare("
                SELECT 1 FROM taxes 
                WHERE vehicle_id = ? 
                  AND start_date = ? 
                  AND end_date = ?
                  AND tax_type_id = ?
            ");
            $check->execute([
                $tax['vehicle_id'],
                $newStartDate,
                $newEndDate,
                $tax['tax_type_id']
            ]);

            if (!$check->fetch()) {
                // Insert new tax record
                $insert = $pdo->prepare("
                    INSERT INTO taxes (vehicle_id, tax_type_id, start_date, end_date, status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([
                    $tax['vehicle_id'],
                    $tax['tax_type_id'],
                    $newStartDate,
                    $newEndDate,
                    'unpaid',
                    $created_by
                ]);
            }
        }
    }
}
?>
