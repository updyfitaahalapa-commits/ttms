<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getroles() {
        $stmt = $this->pdo->prepare("SELECT id, role_name FROM roles");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT u.id,u.full_name,u.username,u.email,u.phone,u.role_id,r.role_name,u.status,
                                     u.created_at FROM users u
                                     INNER JOIN roles r ON r.id=u.role_id
                                     WHERE u.FinishDate IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (
            full_name, username, password, email, phone, role_id, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        return $stmt->execute([
            $data['full_name'],
            $data['username'],
            $data['password'],
            $data['email'],
            $data['phone'],
            $data['role_id'],
            $data['status']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE users SET 
            full_name = ?, username = ?, email = ?, phone = ?, role_id = ?, status = ? 
            WHERE id = ?");

        return $stmt->execute([
            $data['full_name'],
            $data['username'],
            $data['email'],
            $data['phone'],
            $data['role_id'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET FinishDate = ?, deleted_by = ? WHERE id = ?");
        return $stmt->execute([
            date('Y-m-d H:i:s'),
            $_SESSION['user_id'],
            $id
        ]);
    }

}
?>
