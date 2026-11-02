<?php

require_once __DIR__ . '/../core/Model.php';

class Lead extends Model
{
    public function all($filters = [])
    {
        $sql = "SELECT l.*, u.name AS assigned_to_name, creator.name AS created_by_name 
                FROM leads l
                LEFT JOIN users u ON l.assigned_to_id = u.id
                LEFT JOIN users creator ON l.created_by_id = creator.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $sql .= " AND l.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['assigned_to_id']) && $filters['assigned_to_id'] !== 'all') {
            $sql .= " AND l.assigned_to_id = :assigned_to_id";
            $params['assigned_to_id'] = $filters['assigned_to_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE :search OR l.email LIKE :search OR l.company LIKE :search OR l.lead_code LIKE :search OR l.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY l.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $sql = "SELECT l.*, u.name AS assigned_to_name, creator.name AS created_by_name 
                FROM leads l
                LEFT JOIN users u ON l.assigned_to_id = u.id
                LEFT JOIN users creator ON l.created_by_id = creator.id
                WHERE l.id = :id_num OR l.lead_code = :id_code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_num' => is_numeric($id) ? (int)$id : 0,
            'id_code' => (string)$id
        ]);
        return $stmt->fetch();
    }

    public function countAll()
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    }

    public function countByStatus($status)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    public function getStatusDistribution()
    {
        $statuses = ['New' => 0, 'Contacted' => 0, 'Interested' => 0, 'Converted' => 0, 'Lost' => 0];
        $rows = $this->db->query("SELECT status, COUNT(*) as cnt FROM leads GROUP BY status")->fetchAll();
        foreach ($rows as $r) {
            if (isset($statuses[$r['status']])) {
                $statuses[$r['status']] = (int)$r['cnt'];
            }
        }
        return $statuses;
    }

    public function recent($limit = 5)
    {
        $sql = "SELECT l.*, u.name AS assigned_to_name 
                FROM leads l
                LEFT JOIN users u ON l.assigned_to_id = u.id
                ORDER BY l.id DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function generateLeadCode()
    {
        $lastId = (int)$this->db->query("SELECT MAX(id) FROM leads")->fetchColumn();
        return 'LF-' . str_pad($lastId + 1001, 4, '0', STR_PAD_LEFT);
    }

    public function create($data)
    {
        $leadCode = !empty($data['lead_code']) ? $data['lead_code'] : $this->generateLeadCode();
        $sql = "INSERT INTO leads (lead_code, name, email, phone, company, lead_source, status, priority, assigned_to_id, created_by_id, notes, follow_up_date)
                VALUES (:lead_code, :name, :email, :phone, :company, :lead_source, :status, :priority, :assigned_to_id, :created_by_id, :notes, :follow_up_date)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'lead_code' => $leadCode,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'company' => $data['company'],
            'lead_source' => !empty($data['lead_source']) ? $data['lead_source'] : 'Website Form',
            'status' => !empty($data['status']) ? $data['status'] : 'New',
            'priority' => !empty($data['priority']) ? $data['priority'] : 'Medium',
            'assigned_to_id' => !empty($data['assigned_to_id']) ? (int)$data['assigned_to_id'] : null,
            'created_by_id' => (int)$data['created_by_id'],
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'follow_up_date' => !empty($data['follow_up_date']) ? $data['follow_up_date'] : null
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, $data)
    {
        // Support updating by numeric ID or lead_code
        $lead = $this->findById($id);
        if (!$lead) return false;
        $realId = $lead['id'];

        $sql = "UPDATE leads SET 
                name = :name,
                email = :email,
                phone = :phone,
                company = :company,
                lead_source = :lead_source,
                status = :status,
                priority = :priority,
                assigned_to_id = :assigned_to_id,
                notes = :notes,
                follow_up_date = :follow_up_date
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => (int)$realId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'company' => $data['company'],
            'lead_source' => !empty($data['lead_source']) ? $data['lead_source'] : 'Website Form',
            'status' => !empty($data['status']) ? $data['status'] : 'New',
            'priority' => !empty($data['priority']) ? $data['priority'] : 'Medium',
            'assigned_to_id' => !empty($data['assigned_to_id']) ? (int)$data['assigned_to_id'] : null,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'follow_up_date' => !empty($data['follow_up_date']) ? $data['follow_up_date'] : null
        ]);
    }

    public function delete($id)
    {
        $lead = $this->findById($id);
        if (!$lead) return false;
        $realId = $lead['id'];

        $stmt = $this->db->prepare("DELETE FROM leads WHERE id = :id");
        return $stmt->execute(['id' => (int)$realId]);
    }
}
