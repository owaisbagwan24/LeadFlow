<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    public function findByEmail($email)
    {
        $sql = "SELECT id, name, email, password_hash, role, status, created_at FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $sql = "SELECT id, name, email, password_hash, role, status, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($name, $email, $password, $role = 'Employee', $status = 'Active')
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password_hash, role, status)
                VALUES (:name, :email, :password_hash, :role, :status)";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'status' => $status
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    public function all()
    {
        $sql = "SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function countAll()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function updateRole($id, $role)
    {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['role' => $role, 'id' => $id]);
    }

    public function updateProfile($id, $name, $email)
    {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
    }

    public function updatePassword($id, $newPassword)
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = :hash WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['hash' => $passwordHash, 'id' => $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => (int)$id]);
    }
}
