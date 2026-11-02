<?php

require_once __DIR__ . '/../core/Model.php';

class FollowUp extends Model
{
    public function all($filters = [])
    {
        $sql = "SELECT f.*, l.name AS customer_name, l.company AS company_name, l.lead_code,
                       u.name AS assigned_to_name
                FROM followups f
                JOIN leads l ON f.lead_id = l.id
                LEFT JOIN users u ON f.assigned_to_id = u.id
                WHERE 1=1";
        $params = [];

        $status = is_array($filters) ? ($filters['status'] ?? null) : $filters;
        if (!empty($status) && $status !== 'all') {
            $sql .= " AND f.status = :status";
            $params['status'] = $status;
        }

        if (!empty($filters['lead_id'])) {
            $sql .= " AND f.lead_id = :lead_id";
            $params['lead_id'] = (int)$filters['lead_id'];
        }

        $sql .= " ORDER BY f.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $sql = "SELECT f.*, l.name AS customer_name, l.company AS company_name, u.name AS assigned_to_name
                FROM followups f
                JOIN leads l ON f.lead_id = l.id
                LEFT JOIN users u ON f.assigned_to_id = u.id
                WHERE f.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch();
    }

    public function countByStatus($status)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM followups WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    public function countAll()
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM followups")->fetchColumn();
    }

    public function countPending()
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM followups WHERE status != 'completed'")->fetchColumn();
    }

    public function upcoming($limit = 5)
    {
        $sql = "SELECT f.*, l.name AS customer_name, l.company AS company_name, u.name AS assigned_to_name
                FROM followups f
                JOIN leads l ON f.lead_id = l.id
                LEFT JOIN users u ON f.assigned_to_id = u.id
                WHERE f.status != 'completed'
                ORDER BY f.due_date ASC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO followups (lead_id, assigned_to_id, activity_type, due_date, status, notes)
                VALUES (:lead_id, :assigned_to_id, :activity_type, :due_date, :status, :notes)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'lead_id' => (int)$data['lead_id'],
            'assigned_to_id' => !empty($data['assigned_to_id']) ? (int)$data['assigned_to_id'] : null,
            'activity_type' => $data['activity_type'] ?? 'Phone Call',
            'due_date' => $data['due_date'],
            'status' => $data['status'] ?? 'upcoming',
            'notes' => $data['notes'] ?? null
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, $data)
    {
        $sql = "UPDATE followups SET
                    activity_type  = :activity_type,
                    assigned_to_id = :assigned_to_id,
                    due_date       = :due_date,
                    status         = :status,
                    notes          = :notes
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'activity_type'  => $data['activity_type'] ?? 'Phone Call',
            'assigned_to_id' => !empty($data['assigned_to_id']) ? (int)$data['assigned_to_id'] : null,
            'due_date'       => $data['due_date'],
            'status'         => $data['status'] ?? 'upcoming',
            'notes'          => $data['notes'] ?? null,
            'id'             => (int)$id
        ]);
    }

    public function toggleComplete($id)
    {
        $item = $this->findById($id);
        if (!$item) return false;

        $newStatus = ($item['status'] === 'completed') ? 'upcoming' : 'completed';
        $completedAt = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare("UPDATE followups SET status = ?, completed_at = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $completedAt, (int)$id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM followups WHERE id = :id");
        return $stmt->execute(['id' => (int)$id]);
    }
}
