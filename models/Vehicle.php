<?php
class Vehicle {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $sql = "SELECT v.id,v.owner_id,o.full_name AS owner_name,v.plate_number,v.vehicle_type,v.model,v.status,v.RegDate,t.id AS tax_id,t.tax_type_id,t.start_date,t.end_date,t.status AS Tstatus FROM vehicles v
        LEFT JOIN (SELECT * FROM taxes t1 WHERE t1.id = (SELECT MAX(t2.id) FROM taxes t2 
            WHERE t2.vehicle_id = t1.vehicle_id)) t ON t.vehicle_id = v.id
        LEFT JOIN owners o ON o.id = v.owner_id
        WHERE v.FinishDate IS NULL
        ORDER BY v.id ASC;";
        return $this->pdo->query($sql)->fetchAll();
    }


    public function create($pdo, $data) {
        $stmt = $pdo->prepare("INSERT INTO vehicles (
            owner_id, plate_number, vehicle_type, model, status, created_by, RegDate
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())");

        if ($stmt->execute([
            $data['owner_id'],
            $data['plate_number'],
            $data['vehicle_type'],
            $data['model'],
            $data['status'],
            $data['created_by']
        ])) {
            return $pdo->lastInsertId(); // return the new vehicle ID
        }

        return false;
    }



    public function update($pdo, $data) {
        $stmt = $pdo->prepare("UPDATE vehicles SET 
            owner_id = ?, plate_number = ?, vehicle_type = ?, model = ?, status = ?
            WHERE id = ?");
        return $stmt->execute([
            $data['owner_id'],
            $data['plate_number'],
            $data['vehicle_type'],
            $data['model'],
            $data['status'],
            $data['id']
        ]);
    }

    public function transfer($pdo, $data) {
        $stmt = $pdo->prepare("UPDATE vehicles SET owner_id = ? WHERE id = ?");
        return $stmt->execute([$data['new_owner_id'],$data['id']]);
    }


    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE vehicles SET FinishDate = ?, DeletedBy = ? WHERE id = ?");
        return $stmt->execute([
            date('Y-m-d H:i:s'), $_SESSION['user_id'], $id
        ]);
    }
}
?>