<?php
require_once '../config/db.php';

$vehicle_id = $_GET['vehicle_id'] ?? null;

if ($vehicle_id) {
    $stmt = $pdo->prepare("
        SELECT vo.vehicles_owner_id, ol.full_name AS oldOwner, onw.full_name AS newOwner, vo.RegDate 
        FROM vehicles_owner vo
        INNER JOIN owners ol ON ol.id = vo.owner_id
        INNER JOIN owners onw ON onw.id = vo.new_owner_id
        WHERE vo.vehicles_id = ?
        ORDER BY vo.RegDate DESC
    ");
    $stmt->execute([$vehicle_id]);
    $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($owners) {
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Old Owner</th>
                        <th>New Owner</th>
                        <th>Transfer Date</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($owners as $row) {
            echo "<tr>
                    <td>{$row['oldOwner']}</td>
                    <td>{$row['newOwner']}</td>
                    <td>{$row['RegDate']}</td>
                  </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<div class='text-muted'>No owner transfer records found for this vehicle.</div>";
    }
} else {
    echo "<div class='text-danger'>Invalid vehicle ID.</div>";
}

?>