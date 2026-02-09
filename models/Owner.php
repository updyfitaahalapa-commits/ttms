<?php
class Owner {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getDistrictsAll() {
        return $this->pdo->query("SELECT * FROM districts WHERE FinishDate IS NULL ORDER BY `districts`.`name` ASC")->fetchAll();
    }
    public function getAll() {
    $stmt = $this->pdo->query("
        SELECT 
            owners.id AS owner_id,
            owners.district_id,
            owners.full_name,
            owners.ow_id,
            owners.phone,
            owners.email,
            owners.address,
            owners.created_by,
            owners.RegDate AS owner_reg_date,
            owners.FinishDate AS owner_finish_date,
            owners.DeletedBy,
            districts.name AS district_name,
            districts.RegDate AS district_reg_date,
            districts.FinishDate AS district_finish_date
        FROM owners
        INNER JOIN districts ON districts.id = owners.district_id
        WHERE owners.FinishDate IS NULL
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM owners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO owners (district_id,full_name, ow_id, phone, email, address, created_by, RegDate) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$data['district_id'],$data['full_name'],$data['ow_id'],$data['phone'],$data['email'],
            $data['address'], $data['created_by']
        ]);
    }

    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE owners SET district_id=?,full_name=?,ow_id=?,phone=?,email=?,address=? WHERE id=?");
        return $stmt->execute([$data['district_id'],$data['full_name'], $data['ow_id'], $data['phone'], $data['email'], $data['address'], $data['id']
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE owners SET FinishDate = ?, DeletedBy = ? WHERE id = ?");
        return $stmt->execute([date('Y-m-d H:i:s'),$_SESSION['user_id'],$id]);
    }

}
?>