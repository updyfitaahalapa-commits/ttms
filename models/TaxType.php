<?php
// include '../../helpers/session.php';

class TaxType {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Helper method to get current Mogadishu timestamp
    private function CurrentDateandTime() {
        date_default_timezone_set('Africa/Mogadishu');
        return date('Y-m-d H:i:s');
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM tax_types WHERE FinishDate IS NULL");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO tax_types (name, description, amount, frequency, created_by, RegDate) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['amount'],
            $data['frequency'],
            $data['created_by'],
            $this->CurrentDateandTime()
        ]);
    }

    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE tax_types SET name = ?, description = ?, amount = ?, frequency = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['amount'],
            $data['frequency'],
            $data['id']
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE tax_types SET FinishDate = ?, DeletedBy = ? WHERE id = ?");
        return $stmt->execute([$this->CurrentDateandTime(),$_SESSION['user_id'], $id]);
    }
}
?>